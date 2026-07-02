<?php

namespace App\Services;

use App\Models\Ficha;
use App\Models\Pago;
use App\Models\Cliente;
use App\Models\Medico;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    /**
     * Obtener datos para reporte de citas
     */
    public function obtenerDatosCitas($filtros)
    {
        $query = Ficha::with([
            'cliente.usuario.persona',
            'servicio',
            'medico.usuario.persona',
            'sala'
        ]);

        // Aplicar filtros
        if (isset($filtros['fecha_inicio'])) {
            $query->where('fecha', '>=', $filtros['fecha_inicio']);
        }

        if (isset($filtros['fecha_fin'])) {
            $query->where('fecha', '<=', $filtros['fecha_fin']);
        }

        if (isset($filtros['medico_id'])) {
            $query->where('medico_id', $filtros['medico_id']);
        }

        if (isset($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        $fichas = $query->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();

        // Estadísticas
        $estadisticas = [
            'total_citas' => $fichas->count(),
            'por_estado' => $fichas->groupBy('estado')->map->count(),
            'por_medico' => $fichas->groupBy('medico.usuario.persona.nombre')->map->count(),
        ];

        return [
            'fichas' => $fichas,
            'estadisticas' => $estadisticas,
            'filtros' => $filtros,
        ];
    }

    /**
     * Obtener datos para reporte de ingresos
     */
    public function obtenerDatosIngresos($filtros)
    {
        $query = Pago::with([
            'ficha.cliente.usuario.persona',
            'ficha.servicio'
        ])->where('estado', 'PAGADO');

        // Aplicar filtros
        if (isset($filtros['fecha_inicio'])) {
            $query->whereDate('fecha_pago', '>=', $filtros['fecha_inicio']);
        }

        if (isset($filtros['fecha_fin'])) {
            $query->whereDate('fecha_pago', '<=', $filtros['fecha_fin']);
        }

        if (isset($filtros['metodo_pago'])) {
            $query->where('metodo_pago', $filtros['metodo_pago']);
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')->get();

        // Estadísticas
        $estadisticas = [
            'total_ingresos' => $pagos->sum('monto'),
            'total_transacciones' => $pagos->count(),
            'por_metodo' => $pagos->groupBy('metodo_pago')->map(function($grupo) {
                return [
                    'cantidad' => $grupo->count(),
                    'monto' => $grupo->sum('monto')
                ];
            }),
            'promedio_transaccion' => $pagos->avg('monto'),
        ];

        return [
            'pagos' => $pagos,
            'estadisticas' => $estadisticas,
            'filtros' => $filtros,
        ];
    }

    /**
     * Obtener datos para reporte de pacientes por médico
     */
    public function obtenerDatosPacientesPorMedico($filtros)
    {
        $query = Medico::with([
            'usuario.persona',
            'especialidades',
        ])->withCount(['fichas' => function($q) use ($filtros) {
            if (isset($filtros['fecha_inicio'])) {
                $q->where('fecha', '>=', $filtros['fecha_inicio']);
            }
            if (isset($filtros['fecha_fin'])) {
                $q->where('fecha', '<=', $filtros['fecha_fin']);
            }
        }]);

        if (isset($filtros['medico_id'])) {
            $query->where('usuario_id', $filtros['medico_id']);
        }

        $medicos = $query->get();

        // Obtener detalles de pacientes por médico
        $detallesPorMedico = [];
        foreach ($medicos as $medico) {
            $fichasQuery = Ficha::with(['cliente.usuario.persona', 'servicio'])
                ->where('medico_id', $medico->usuario_id);
            
            if (isset($filtros['fecha_inicio'])) {
                $fichasQuery->where('fecha', '>=', $filtros['fecha_inicio']);
            }
            if (isset($filtros['fecha_fin'])) {
                $fichasQuery->where('fecha', '<=', $filtros['fecha_fin']);
            }

            $fichas = $fichasQuery->get();

            $detallesPorMedico[$medico->usuario_id] = [
                'medico' => $medico,
                'total_consultas' => $fichas->count(),
                'pacientes_unicos' => $fichas->pluck('cliente_id')->unique()->count(),
                'fichas' => $fichas,
            ];
        }

        // Estadísticas generales
        $estadisticas = [
            'total_medicos' => $medicos->count(),
            'total_consultas' => collect($detallesPorMedico)->sum('total_consultas'),
            'promedio_por_medico' => $medicos->count() > 0 
                ? collect($detallesPorMedico)->avg('total_consultas') 
                : 0,
        ];

        return [
            'medicos' => $detallesPorMedico,
            'estadisticas' => $estadisticas,
            'filtros' => $filtros,
        ];
    }
}

