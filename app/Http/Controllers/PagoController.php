<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ficha;
use App\Models\Pago;
use App\Models\MetodoPago;
use App\Models\PlanCuota;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PagoController extends Controller
{
    /**
     * Página principal de pagos
     */
    public function paginaPrincipalPago(Request $solicitud)
    {
        $usuario = $solicitud->user();

        if ($usuario->hasRole('Medico') && ! $usuario->hasAnyRole(['Administrador', 'Secretaria'])) {
            abort(403, 'Los médicos no tienen acceso al módulo de pagos.');
        }

        // ✅ Flujo profesional:
        // - Cliente: "Mis Pagos" (solo los suyos)
        // - Secretaria/Admin: "Pagos (Consultorio)" (global con filtros)
        $esStaff = method_exists($usuario, 'hasAnyRole')
            ? $usuario->hasAnyRole(['Secretaria', 'Administrador'])
            : false;

        if ($esStaff) {
            $buscar = trim((string) $solicitud->get('buscar', ''));
            $estado = $solicitud->get('estado'); // PAGADO|PENDIENTE|ANULADO|null
            $metodo = $solicitud->get('metodo_pago'); // EFECTIVO|QR|null
            $desde = $solicitud->get('desde');
            $hasta = $solicitud->get('hasta');

            $query = Pago::query()
                ->with([
                    'ficha.servicio',
                    'ficha.medico.usuario.persona',
                    'ficha.cliente.usuario.persona',
                ])
                ->orderByDesc('fecha_pago')
                ->orderByDesc('created_at');

            if ($estado) {
                $query->where('estado', $estado);
            }

            if ($metodo) {
                $query->where('metodo_pago', $metodo);
            }

            if ($desde) {
                $query->whereDate('fecha_pago', '>=', $desde);
            }

            if ($hasta) {
                $query->whereDate('fecha_pago', '<=', $hasta);
            }

            if ($buscar !== '') {
                $query->whereHas('ficha', function ($q) use ($buscar) {
                    $q->whereHas('cliente.usuario.persona', function ($p) use ($buscar) {
                        $p->where('dni', 'like', '%' . $buscar . '%')
                          ->orWhere('nombre', 'like', '%' . $buscar . '%')
                          ->orWhere('apellidos', 'like', '%' . $buscar . '%');
                    })->orWhereHas('servicio', function ($s) use ($buscar) {
                        $s->where('nombre', 'like', '%' . $buscar . '%');
                    });
                });
            }

            $pagos = $query->paginate(15)->withQueryString();

            return Inertia::render('Pagos/ClinicaIndex', [
                'pagos' => $pagos,
                'filtros' => [
                    'buscar' => $buscar,
                    'estado' => $estado,
                    'metodo_pago' => $metodo,
                    'desde' => $desde,
                    'hasta' => $hasta,
                ],
            ]);
        }

        // Cliente (y otros roles sin acceso): obtener pagos del usuario
        $pagos = Pago::obtenerPorUsuario($usuario->id)
            ->with(['ficha.servicio', 'ficha.medico.usuario.persona', 'ficha.sala', 'ficha.pagos'])
            ->paginate(10);

        $pagos->getCollection()->transform(function (Pago $pago) {
            if ($pago->ficha instanceof Ficha) {
                $ficha = $pago->ficha;
                $ficha->total_pagado = $ficha->calcularTotalPagado();
                $ficha->saldo_pendiente = $ficha->calcularSaldoPendiente();
                $ficha->costo_total = $ficha->obtenerCostoNetoAcordado();
                $ficha->tiene_pago_pendiente = $ficha->pagos->contains(
                    fn ($item) => $item->estado === 'PENDIENTE'
                );
            }

            return $pago;
        });

        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
        ]);
    }

    /**
     * Guardar nuevo pago único
     */
    public function guardarPagoUnico(Request $datos)
    {
        $usuario = $datos->user();

        $datos->validate([
            'ficha_id' => 'required|string|exists:fichas,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago_id' => 'nullable|exists:metodos_pago,id',
            'metodo_pago' => 'required|in:EFECTIVO,TARJETA,TRANSFERENCIA',
            'comprobante_url' => 'nullable|string|max:255',
        ], [
            'ficha_id.required' => 'La ficha es obligatoria.',
            'ficha_id.exists' => 'La ficha seleccionada no existe.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número válido.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'metodo_pago_id.exists' => 'El método de pago seleccionado no existe.',
            'metodo_pago.required' => 'El método de pago es obligatorio.',
            'metodo_pago.in' => 'El método de pago seleccionado no es válido.',
        ]);

        // Verificar que el método de pago pertenezca al usuario si se proporciona
        if ($datos->metodo_pago_id) {
            $metodoPago = MetodoPago::where('usuario_id', $usuario->id)
                ->findOrFail($datos->metodo_pago_id);
        }

        $pago = Pago::create([
            'id' => Str::uuid()->toString(),
            'ficha_id' => $datos->ficha_id,
            'metodo_pago_id' => $datos->metodo_pago_id,
            'monto' => $datos->monto,
            'tipo' => 'CONTADO',
            'fecha_pago' => now(),
            'metodo_pago' => $datos->metodo_pago,
            'comprobante_url' => $datos->comprobante_url,
            'estado' => 'PAGADO',
        ]);

        return redirect()->route('pagos.index')
            ->with('success', 'Pago registrado exitosamente.');
    }

    /**
     * Guardar pago de cuota
     */
    public function guardarPagoCuota(Request $datos)
    {
        $usuario = $datos->user();

        $datos->validate([
            'plan_cuota_id' => 'required|string|exists:planes_cuota,id',
            'numero_cuota' => 'required|integer|min:1',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago_id' => 'nullable|exists:metodos_pago,id',
            'metodo_pago' => 'required|in:EFECTIVO,TARJETA,TRANSFERENCIA',
            'comprobante_url' => 'nullable|string|max:255',
        ], [
            'plan_cuota_id.required' => 'El plan de cuota es obligatorio.',
            'plan_cuota_id.exists' => 'El plan de cuota seleccionado no existe.',
            'numero_cuota.required' => 'El número de cuota es obligatorio.',
            'numero_cuota.integer' => 'El número de cuota debe ser un número entero.',
            'numero_cuota.min' => 'El número de cuota debe ser mayor a 0.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número válido.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'metodo_pago_id.exists' => 'El método de pago seleccionado no existe.',
            'metodo_pago.required' => 'El método de pago es obligatorio.',
            'metodo_pago.in' => 'El método de pago seleccionado no es válido.',
        ]);

        $planCuota = PlanCuota::findOrFail($datos->plan_cuota_id);

        // Verificar que el método de pago pertenezca al usuario si se proporciona
        if ($datos->metodo_pago_id) {
            $metodoPago = MetodoPago::where('usuario_id', $usuario->id)
                ->findOrFail($datos->metodo_pago_id);
        }

        $pago = Pago::create([
            'id' => Str::uuid()->toString(),
            'plan_cuota_id' => $datos->plan_cuota_id,
            'ficha_id' => $planCuota->ficha_id,
            'metodo_pago_id' => $datos->metodo_pago_id,
            'monto' => $datos->monto,
            'tipo' => 'CUOTA',
            'numero_cuota' => $datos->numero_cuota,
            'fecha_pago' => now(),
            'metodo_pago' => $datos->metodo_pago,
            'comprobante_url' => $datos->comprobante_url,
            'estado' => 'PAGADO',
        ]);

        return redirect()->route('pagos.index')
            ->with('success', 'Pago de cuota registrado exitosamente.');
    }

    /**
     * Mostrar detalles de un pago
     */
    public function mostrarPago(string $id)
    {
        $usuario = auth()->user();
        $pago = Pago::with(['metodoPago', 'planCuota'])
            ->whereHas('metodoPago', function ($query) use ($usuario) {
                $query->where('usuario_id', $usuario->id);
            })
            ->findOrFail($id);

        return response()->json(['pago' => $pago]);
    }
}
