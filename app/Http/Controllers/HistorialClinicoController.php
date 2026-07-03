<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HistorialClinico;
use App\Models\Cliente;
use Inertia\Inertia;
use App\Http\Requests\StoreHistorialClinicoRequest;
use App\Http\Requests\UpdateHistorialClinicoRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class HistorialClinicoController extends Controller
{
    private function autorizarHistoriales(string ...$permisos): void
    {
        $usuario = auth()->user();
        foreach ($permisos as $permiso) {
            if ($usuario->can($permiso)) {
                return;
            }
        }
        abort(403);
    }

    /**
     * Display a listing of the resource.
     */
    public function paginaPrincipalHistorialClinico(Request $request)
    {
        $this->autorizarHistoriales('ver-historiales-clinicos', 'gestionar-historiales-clinicos');

        HistorialClinico::sincronizarHistorialesFaltantes();

        // Query base con relaciones
        $query = HistorialClinico::with([
            'cliente.usuario.persona',
            'cliente.fichas' => function($q) {
                $q->where('estado', 'ATENDIDA')
                  ->orderBy('fecha', 'desc')
                  ->limit(1);
            }
        ]);

        // Búsqueda por nombre o DNI
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->whereHas('cliente.usuario.persona', function($q) use ($busqueda) {
                $q->where('nombre', 'ILIKE', "%{$busqueda}%")
                  ->orWhere('apellidos', 'ILIKE', "%{$busqueda}%")
                  ->orWhere('dni', 'LIKE', "%{$busqueda}%");
            });
        }

        // Filtro por grupo sanguíneo faltante
        if ($request->boolean('sin_grupo_sanguineo')) {
            $query->whereNull('grupo_sanguineo')
                  ->orWhere('grupo_sanguineo', '');
        }

        // Filtro por alergias
        if ($request->filled('con_alergias')) {
            $query->whereNotNull('alergias')
                  ->where('alergias', '!=', '');
        }

        // Filtro por completitud
        if ($request->filled('completitud')) {
            if ($request->completitud === 'incompleto') {
                $query->where(function($q) {
                    $q->whereNull('grupo_sanguineo')
                      ->orWhereNull('factor_rh')
                      ->orWhere('grupo_sanguineo', '')
                      ->orWhere('factor_rh', '');
                });
            } elseif ($request->completitud === 'completo') {
                $query->whereNotNull('grupo_sanguineo')
                      ->whereNotNull('factor_rh')
                      ->where('grupo_sanguineo', '!=', '')
                      ->where('factor_rh', '!=', '');
            }
        }

        // Ordenamiento
        $ordenarPor = $request->get('ordenar', 'updated_at');
        $direccion = $request->get('direccion', 'desc');
        
        if ($ordenarPor === 'paciente') {
            $query->join('clientes', 'historiales_clinicos.cliente_id', '=', 'clientes.usuario_id')
                  ->join('usuarios', 'clientes.usuario_id', '=', 'usuarios.id')
                  ->join('personas', 'usuarios.persona_id', '=', 'personas.id')
                  ->orderBy('personas.nombre', $direccion)
                  ->select('historiales_clinicos.*');
        } else {
            $query->orderBy($ordenarPor, $direccion);
        }

        $historiales = $query->paginate(15)->withQueryString();

        // Agregar información calculada
        $historiales->getCollection()->transform(function($historial) {
            $historial->completitud = $this->calcularCompletitud($historial);
            $historial->ultima_consulta = $historial->cliente->fichas->first();
            return $historial;
        });

        $contadorVisitas = DB::table('visitas_paginas')
            ->where('ruta', 'historiales-clinicos')
            ->count();

        return Inertia::render('HistorialesClinicos/Index', [
            'historiales' => $historiales,
            'contadorVisitas' => $contadorVisitas,
            'filtros' => $request->only(['busqueda', 'sin_grupo_sanguineo', 'con_alergias', 'completitud', 'ordenar', 'direccion']),
        ]);
    }

    /**
     * Calcular completitud del historial
     */
    private function calcularCompletitud($historial)
    {
        $camposRequeridos = [
            'grupo_sanguineo',
            'factor_rh',
            'alergias',
            'enfermedades_cronicas',
            'medicamentos_habituales',
            'peso_habitual',
            'estatura',
            'antecedentes_familiares',
            'antecedentes_quirurgicos',
        ];

        $completados = 0;
        foreach ($camposRequeridos as $campo) {
            if (!empty($historial->$campo)) {
                $completados++;
            }
        }

        return round(($completados / count($camposRequeridos)) * 100);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function crearHistorialClinico()
    {
        $this->authorize('crear-historiales-clinicos');

        $clientes = Cliente::with('usuario.persona')
            ->whereDoesntHave('historialClinico')
            ->get();

        return Inertia::render('HistorialesClinicos/Create', [
            'clientes' => $clientes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function guardarHistorialClinico(StoreHistorialClinicoRequest $datos)
    {
        $historialId = Str::uuid()->toString();
        HistorialClinico::create([
            'id' => $historialId,
            'cliente_id' => $datos->cliente_id,
            'alergias' => $datos->alergias,
            'enfermedades_cronicas' => $datos->enfermedades_cronicas,
            'medicamentos_habituales' => $datos->medicamentos_habituales,
        ]);

        return redirect()->route('historiales-clinicos.index')
            ->with('success', 'Historial clínico creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function mostrarHistorialClinico(string $id)
    {
        $this->authorize('mostrar-historiales-clinicos');

        $historial = HistorialClinico::with([
            'cliente.usuario.persona',
            'cliente.fichas' => function($q) {
                $q->with(['servicio', 'medico.usuario.persona', 'seguimientos'])
                  ->orderBy('fecha', 'desc');
            }
        ])->findOrFail($id);

        // Calcular estadísticas
        $todasLasFichas = $historial->cliente->fichas;
        $estadisticas = [
            'total_consultas' => $todasLasFichas->where('estado', 'ATENDIDA')->count(),
            'ultima_consulta' => $todasLasFichas->first(),
            'consultas_ultimo_año' => $todasLasFichas->where('fecha', '>=', now()->subYear())->count(),
            'servicios_frecuentes' => $todasLasFichas->groupBy('servicio_id')->map->count()->sortDesc()->take(3),
        ];

        return response()->json([
            'historial' => $historial,
            'estadisticas' => $estadisticas,
            'completitud' => $this->calcularCompletitud($historial),
        ]);
    }

    /**
     * Ver historial completo integrado (nueva funcionalidad)
     */
    public function verHistorialCompleto(string $id)
    {
        $this->authorize('mostrar-historiales-clinicos');

        $historial = HistorialClinico::with([
            'cliente.usuario.persona',
            'cliente.fichas' => function($q) {
                $q->with([
                    'servicio',
                    'medico.usuario.persona',
                    'seguimientos' => function($sq) {
                        $sq->with('medico.usuario.persona')
                           ->orderBy('fecha', 'desc');
                    }
                ])->orderBy('fecha', 'desc');
            }
        ])->findOrFail($id);

        return Inertia::render('HistorialesClinicos/VistaCompleta', [
            'historial' => $historial,
            'completitud' => $this->calcularCompletitud($historial),
        ]);
    }

    /**
     * Exportar historial a PDF
     */
    public function exportarPDF(string $id)
    {
        $this->authorize('mostrar-historiales-clinicos');

        $historial = HistorialClinico::with([
            'cliente.usuario.persona',
            'cliente.fichas.servicio',
            'cliente.fichas.medico.usuario.persona',
            'cliente.fichas.seguimientos'
        ])->findOrFail($id);

        $pdf = \PDF::loadView('historiales.pdf', [
            'historial' => $historial,
            'paciente' => $historial->cliente->usuario->persona,
        ]);

        return $pdf->download("historial_{$historial->cliente->usuario->persona->dni}.pdf");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editarHistorialClinico(string $id)
    {
        $this->authorize('editar-historiales-clinicos');

        $historial = HistorialClinico::with('cliente.usuario.persona')
            ->findOrFail($id);

        return Inertia::render('HistorialesClinicos/Edit', [
            'historial' => $historial,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function actualizarHistorialClinico(UpdateHistorialClinicoRequest $datos, string $id)
    {
        $historial = HistorialClinico::findOrFail($id);

        $historial->update([
            'alergias' => $datos->alergias,
            'enfermedades_cronicas' => $datos->enfermedades_cronicas,
            'medicamentos_habituales' => $datos->medicamentos_habituales,
        ]);

        return redirect()->route('historiales-clinicos.index')
            ->with('success', 'Historial clínico actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function eliminarHistorialClinico(string $id)
    {
        $this->authorize('eliminar-historiales-clinicos');

        $historial = HistorialClinico::findOrFail($id);

        $historial->delete();

        return redirect()->route('historiales-clinicos.index')
            ->with('success', 'Historial clínico eliminado exitosamente.');
    }
}

