<?php

namespace App\Http\Controllers;

use App\Models\Ficha;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecepcionController extends Controller
{
    /**
     * Panel de recepción: check-in de pacientes del día.
     */
    public function index()
    {
        $this->authorize('gestionar-fichas');

        $hoy = now()->toDateString();

        $programadas = Ficha::with([
            'cliente.usuario.persona',
            'medico.usuario.persona',
            'servicio',
            'sala',
        ])
            ->whereDate('fecha', $hoy)
            ->programadas()
            ->orderBy('hora')
            ->get();

        $enEspera = Ficha::with([
            'cliente.usuario.persona',
            'medico.usuario.persona',
            'servicio',
            'sala',
        ])
            ->whereDate('fecha', $hoy)
            ->enEspera()
            ->orderBy('fecha_llegada')
            ->get();

        $estadisticas = [
            'programadas' => $programadas->count(),
            'en_espera' => $enEspera->count(),
            'en_atencion' => Ficha::whereDate('fecha', $hoy)->enAtencion()->count(),
            'atendidas' => Ficha::whereDate('fecha', $hoy)->where('estado', 'ATENDIDA')->count(),
            'total_dia' => Ficha::whereDate('fecha', $hoy)->count(),
        ];

        return Inertia::render('Recepcion/Index', [
            'programadas' => $programadas,
            'en_espera' => $enEspera,
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * Registrar llegada física del paciente (check-in).
     */
    public function marcarLlegada(string $fichaId)
    {
        $this->authorize('gestionar-fichas');

        $ficha = Ficha::findOrFail($fichaId);

        if (!$ficha->puedeMarcarLlegada()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta ficha no puede registrar llegada en su estado actual.',
            ], 400);
        }

        $ficha->marcarLlegada();

        return response()->json([
            'success' => true,
            'message' => 'Llegada registrada. El paciente está en sala de espera.',
            'ficha' => $ficha->fresh(['cliente.usuario.persona', 'medico.usuario.persona', 'servicio', 'sala']),
        ]);
    }
}
