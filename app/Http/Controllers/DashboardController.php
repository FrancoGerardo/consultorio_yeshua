<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Ficha;
use App\Models\HistorialClinico;
use App\Models\Medico;
use App\Models\Pago;
use App\Models\Seguimiento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $rolOriginal = $usuario->getRoleNames()->first() ?? 'SIN_ROL';
        
        // Normalizar el nombre del rol a mayúsculas para comparación
        $rol = strtoupper($rolOriginal);

        // Métricas generales (para todos)
        $metricas = $this->obtenerMetricasGenerales();

        // Datos específicos según el rol
        $datosRol = [];

        switch ($rol) {
            case 'PROPIETARIO':
            case 'ADMINISTRADOR':
                $datosRol = $this->obtenerDatosAdmin();
                break;
            case 'MEDICO':
            case 'MÉDICO':
                $datosRol = $this->obtenerDatosMedico($usuario);
                break;
            case 'SECRETARIA':
                $datosRol = $this->obtenerDatosSecretaria();
                break;
            case 'CLIENTE':
                $datosRol = $this->obtenerDatosCliente($usuario);
                break;
        }

        return Inertia::render('Dashboard', [
            'rol' => $rol,
            'rolOriginal' => $rolOriginal,
            'usuario' => $usuario->load('persona'),
            'metricas' => $metricas,
            'datosRol' => $datosRol,
        ]);
    }

    // ==================== MÉTRICAS GENERALES ====================
    
    private function obtenerMetricasGenerales()
    {
        $hoy = Carbon::today();
        $mesActual = Carbon::now()->month;
        $anioActual = Carbon::now()->year;

        return [
            // Pacientes atendidos hoy
            'pacientes_atendidos_hoy' => Ficha::whereDate('fecha', $hoy)
                ->where('estado', 'ATENDIDA')
                ->count(),

            // Total de fichas del día
            'citas_hoy' => Ficha::whereDate('fecha', $hoy)->count(),
            'citas_completadas_hoy' => Ficha::whereDate('fecha', $hoy)
                ->where('estado', 'ATENDIDA')
                ->count(),
            'citas_pendientes_hoy' => Ficha::whereDate('fecha', $hoy)
                ->whereIn('estado', ['EN_ESPERA', 'EN_ATENCION'])
                ->count(),

            // Ingresos del día
            'ingresos_hoy' => Pago::whereDate('fecha_pago', $hoy)
                ->where('estado', 'COMPLETADO')
                ->sum('monto'),

            // Ingresos del mes
            'ingresos_mes' => Pago::whereMonth('fecha_pago', $mesActual)
                ->whereYear('fecha_pago', $anioActual)
                ->where('estado', 'COMPLETADO')
                ->sum('monto'),

            // Pacientes en espera
            'pacientes_en_espera' => Ficha::whereDate('fecha', $hoy)
                ->whereIn('estado', ['EN_ESPERA', 'EN_ATENCION'])
                ->count(),

            // Médicos activos (tienen fichas hoy)
            'medicos_activos' => Medico::whereHas('fichas', function ($query) use ($hoy) {
                $query->whereDate('fecha', $hoy);
            })->count(),

            // Total de médicos
            'total_medicos' => Medico::count(),
        ];
    }

    // ==================== DATOS ESPECÍFICOS POR ROL ====================

    private function obtenerDatosAdmin()
    {
        $hoy = Carbon::today();
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();

        return [
            // Gráfico de fichas de la semana
            'grafico_citas_semana' => $this->obtenerFichasSemana(),

            // Servicios más solicitados
            'servicios_populares' => $this->obtenerServiciosPopulares(),

            // Ingresos por día (última semana)
            'grafico_ingresos' => $this->obtenerIngresosSemana(),

            // Fichas de hoy con detalles
            'citas_hoy' => Ficha::with(['cliente.usuario.persona', 'medico.usuario.persona', 'servicio'])
                ->whereDate('fecha', $hoy)
                ->orderBy('hora')
                ->get(),

            // Resumen financiero
            'resumen_financiero' => [
                'pagos_pendientes' => Pago::where('estado', 'PENDIENTE')->count(),
                'monto_pendiente' => Pago::where('estado', 'PENDIENTE')->sum('monto'),
                'total_pacientes' => Cliente::count(),
            ],
        ];
    }

    private function obtenerDatosMedico($usuario)
    {
        $hoy = Carbon::today();
        $medico = $usuario->medico;

        if (!$medico) {
            return [];
        }

        return [
            // Cola de pacientes del médico
            'cola_pacientes' => Ficha::with('cliente.usuario.persona')
                ->whereDate('fecha', $hoy)
                ->where('medico_id', $medico->usuario_id)
                ->whereIn('estado', ['EN_ESPERA', 'EN_ATENCION'])
                ->orderBy('hora')
                ->get(),

            // Fichas del día
            'citas_hoy' => Ficha::with(['cliente.usuario.persona', 'servicio'])
                ->whereDate('fecha', $hoy)
                ->where('medico_id', $medico->usuario_id)
                ->orderBy('hora')
                ->get(),

            // Resumen del médico
            'resumen' => [
                'pacientes_atendidos_hoy' => Ficha::whereDate('fecha', $hoy)
                    ->where('medico_id', $medico->usuario_id)
                    ->where('estado', 'ATENDIDA')
                    ->count(),
                'pacientes_pendientes' => Ficha::whereDate('fecha', $hoy)
                    ->where('medico_id', $medico->usuario_id)
                    ->whereIn('estado', ['EN_ESPERA', 'EN_ATENCION'])
                    ->count(),
                'especialidad' => $medico->especialidad->nombre ?? 'N/A',
            ],
        ];
    }

    private function obtenerDatosSecretaria()
    {
        $hoy = Carbon::today();

        return [
            // Todas las fichas del día
            'citas_hoy' => Ficha::with(['cliente.usuario.persona', 'medico.usuario.persona', 'servicio'])
                ->whereDate('fecha', $hoy)
                ->orderBy('hora')
                ->get(),

            // Pacientes en sala de espera
            'pacientes_espera' => Ficha::with('cliente.usuario.persona', 'medico.usuario.persona')
                ->whereDate('fecha', $hoy)
                ->whereIn('estado', ['EN_ESPERA', 'EN_ATENCION'])
                ->orderBy('hora')
                ->get(),

            // Próximas fichas - Limitado a las primeras 10 pendientes
            'proximas_citas' => Ficha::with(['cliente.usuario.persona', 'medico.usuario.persona'])
                ->whereDate('fecha', $hoy)
                ->whereIn('estado', ['EN_ESPERA', 'EN_ATENCION'])
                ->orderBy('hora')
                ->limit(10)
                ->get(),

            // Resumen
            'resumen' => [
                'citas_confirmadas' => Ficha::whereDate('fecha', $hoy)
                    ->where('estado', 'ATENDIDA')
                    ->count(),
                'citas_pendientes_confirmacion' => Ficha::whereDate('fecha', $hoy)
                    ->whereIn('estado', ['EN_ESPERA', 'EN_ATENCION'])
                    ->count(),
            ],
        ];
    }

    private function obtenerDatosCliente($usuario)
    {
        $cliente = $usuario->cliente;

        if (!$cliente) {
            return [];
        }

        return [
            // Próximas fichas del cliente
            'proximas_citas' => Ficha::with(['medico.usuario.persona', 'servicio'])
                ->where('cliente_id', $cliente->usuario_id)
                ->whereDate('fecha', '>=', Carbon::today())
                ->whereIn('estado', ['EN_ESPERA', 'EN_ATENCION'])
                ->orderBy('fecha')
                ->orderBy('hora')
                ->limit(5)
                ->get(),

            // Historial clínico resumido
            'historial' => HistorialClinico::where('cliente_id', $cliente->usuario_id)
                ->first(),

            // Últimas consultas
            'ultimas_consultas' => Seguimiento::with('medico.usuario.persona')
                ->whereHas('ficha', function ($query) use ($cliente) {
                    $query->where('cliente_id', $cliente->usuario_id);
                })
                ->orderBy('fecha', 'desc')
                ->limit(3)
                ->get(),

            // Resumen
            'resumen' => [
                'total_citas' => Ficha::where('cliente_id', $cliente->usuario_id)->count(),
                'proxima_cita' => Ficha::where('cliente_id', $cliente->usuario_id)
                    ->whereDate('fecha', '>=', Carbon::today())
                    ->whereIn('estado', ['EN_ESPERA', 'EN_ATENCION'])
                    ->orderBy('fecha')
                    ->orderBy('hora')
                    ->first(),
            ],
        ];
    }

    // ==================== GRÁFICOS ====================

    private function obtenerFichasSemana()
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();

        $fichas = Ficha::whereBetween('fecha', [$inicioSemana, $finSemana])
            ->selectRaw('DATE(fecha) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $labels = [];
        $datos = [];

        for ($i = 0; $i < 7; $i++) {
            $fecha = $inicioSemana->copy()->addDays($i);
            $labels[] = $dias[$fecha->dayOfWeek] . ' ' . $fecha->format('d');
            
            $ficha = $fichas->firstWhere('fecha', $fecha->format('Y-m-d'));
            $datos[] = $ficha ? $ficha->total : 0;
        }

        return [
            'labels' => $labels,
            'datos' => $datos,
        ];
    }

    private function obtenerIngresosSemana()
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();

        $ingresos = Pago::whereBetween('fecha_pago', [$inicioSemana, $finSemana])
            ->where('estado', 'COMPLETADO')
            ->selectRaw('DATE(fecha_pago) as fecha, SUM(monto) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $labels = [];
        $datos = [];

        for ($i = 0; $i < 7; $i++) {
            $fecha = $inicioSemana->copy()->addDays($i);
            $labels[] = $dias[$fecha->dayOfWeek] . ' ' . $fecha->format('d');
            
            $ingreso = $ingresos->firstWhere('fecha', $fecha->format('Y-m-d'));
            $datos[] = $ingreso ? (float)$ingreso->total : 0;
        }

        return [
            'labels' => $labels,
            'datos' => $datos,
        ];
    }

    private function obtenerServiciosPopulares()
    {
        $inicioMes = Carbon::now()->startOfMonth();

        return Ficha::with('servicio')
            ->whereDate('fecha', '>=', $inicioMes)
            ->select('servicio_id', DB::raw('COUNT(*) as total'))
            ->groupBy('servicio_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->servicio->nombre ?? 'N/A',
                    'total' => $item->total,
                ];
            });
    }
}

