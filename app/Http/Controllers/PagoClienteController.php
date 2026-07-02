<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ficha;
use App\Models\Pago;
use App\Models\Cliente;
use App\Models\ConfiguracionPago;
use App\Services\PagoFacilService;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PagoClienteController extends Controller
{
    protected $pagoFacilService;

    public function __construct(PagoFacilService $pagoFacilService)
    {
        $this->pagoFacilService = $pagoFacilService;
    }

    /**
     * Mostrar pantalla de selección de plan de pago (NUEVA LÓGICA)
     */
    public function seleccionarPlanPago(string $fichaId)
    {
        $ficha = Ficha::with(['servicio', 'medico.usuario.persona', 'cliente.usuario.persona'])
            ->findOrFail($fichaId);

        // Verificar que la ficha pertenece al usuario autenticado
        if ($ficha->cliente_id !== auth()->id()) {
            return redirect()->route('cliente.fichas.index')
                ->with('error', 'No tienes permiso para ver esta ficha.');
        }

        if ($ficha->estado === 'CANCELADA') {
            return redirect()->route('cliente.fichas.index')
                ->with('error', 'Esta ficha fue cancelada.');
        }

        if ($ficha->estado !== 'PENDIENTE_PAGO') {
            if ($ficha->estado === 'ANTICIPO_PAGADO' && $ficha->calcularSaldoPendiente() > 0) {
                return redirect()->route('cliente.pagos.procesar', $fichaId);
            }

            return redirect()->route('cliente.fichas.index')
                ->with('error', 'Esta ficha no requiere selección de plan de pago.');
        }

        // Obtener o crear configuración de pago para el servicio
        $configuracion = ConfiguracionPago::obtenerOCrearParaServicio($ficha->servicio_id);

        $costoTotal = (float) ($ficha->servicio->costo ?? 0);

        // Calcular opciones de pago
        $opcionesPago = [
            // OPCIÓN A: Pago Total (con descuento)
            'pago_total' => [
                'disponible' => $configuracion->permite_pago_total,
                'monto_original' => $costoTotal,
                'descuento' => $configuracion->calcularDescuentoPagoTotal($costoTotal),
                'monto_final' => $costoTotal - $configuracion->calcularDescuentoPagoTotal($costoTotal),
                'porcentaje_descuento' => $configuracion->descuento_pago_total,
            ],
            
            // OPCIÓN B: Anticipo + Saldo (Estándar)
            'anticipo_saldo' => [
                'disponible' => true,
                'monto_anticipo' => $configuracion->calcularMontoAnticipo($costoTotal, false),
                'porcentaje_anticipo' => $configuracion->porcentaje_anticipo_minimo,
                'monto_saldo' => $costoTotal - $configuracion->calcularMontoAnticipo($costoTotal, false),
                'porcentaje_saldo' => 100 - $configuracion->porcentaje_anticipo_minimo,
            ],
            
            // OPCIÓN C: Plan de Cuotas
            'plan_cuotas' => [
                'disponible' => $configuracion->calificaParaCuotas($costoTotal),
                'monto_anticipo' => $configuracion->calcularMontoAnticipo($costoTotal, true),
                'porcentaje_anticipo' => $configuracion->porcentaje_anticipo_cuotas,
                'monto_restante' => $costoTotal - $configuracion->calcularMontoAnticipo($costoTotal, true),
                'max_cuotas' => $configuracion->max_cuotas,
                'intervalo_dias' => $configuracion->intervalo_dias_cuota,
                'monto_minimo_requerido' => $configuracion->monto_minimo_cuotas,
            ],
        ];

        return Inertia::render('PagosCliente/SeleccionarPlan', [
            'ficha' => $ficha,
            'costoTotal' => (float) $costoTotal,
            'opciones' => $opcionesPago,
            'configuracion' => $configuracion,
        ]);
    }

    /**
     * Procesar pago según el plan seleccionado
     */
    public function procesarPago(string $fichaId)
    {
        $ficha = Ficha::with(['servicio', 'medico.usuario.persona', 'cliente.usuario.persona', 'pagos'])
            ->findOrFail($fichaId);

        // Verificar que la ficha pertenece al usuario autenticado
        if ($ficha->cliente_id !== auth()->id()) {
            return redirect()->route('cliente.fichas.index')
                ->with('error', 'No tienes permiso para ver esta ficha.');
        }

        // Calcular información de pago
        $costoTotal = (float) ($ficha->servicio->costo ?? 0);
        $totalPagado = (float) $ficha->calcularTotalPagado();
        $saldoPendiente = (float) $ficha->calcularSaldoPendiente();
        $porcentajePagado = (float) $ficha->calcularPorcentajePagado();

        // Determinar qué tipo de pago se debe hacer
        $tienePagoAnticipo = $ficha->tienePagoAnticipo();
        $tipoPagoRequerido = $tienePagoAnticipo ? 'SALDO' : 'ANTICIPO';

        // Verificar si ya hay un pago pendiente con QR
        $pagoPendiente = $ficha->pagos()
            ->where('metodo_pago', 'QR')
            ->where('qr_status', 'PENDING')
            ->where('estado', 'PENDIENTE')
            ->orderBy('created_at', 'desc')
            ->first();

        return Inertia::render('PagosCliente/Procesar', [
            'ficha' => $ficha,
            'costoTotal' => (float) $costoTotal,
            'totalPagado' => (float) $totalPagado,
            'saldoPendiente' => (float) $saldoPendiente,
            'porcentajePagado' => (float) $porcentajePagado,
            'tipoPagoRequerido' => $tipoPagoRequerido,
            'pagoPendiente' => $pagoPendiente,
        ]);
    }

    /**
     * Generar QR para pago (ACTUALIZADO)
     */
    public function generarQr(Request $request)
    {
        $request->validate([
            'ficha_id' => 'required|string|exists:fichas,id',
            'plan_pago' => 'required|string|in:TOTAL,ANTICIPO,SALDO',
            'monto' => 'required|numeric|min:0.01',
        ]);

        $ficha = Ficha::with(['servicio', 'cliente.usuario.persona'])->findOrFail($request->ficha_id);

        // Verificar que la ficha pertenece al usuario autenticado
        if ($ficha->cliente_id !== auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'No tienes permiso para esta ficha.']);
        }

        $saldoPendiente = $ficha->calcularSaldoPendiente();

        if ($saldoPendiente <= 0) {
            return redirect()->back()->withErrors(['error' => 'Esta ficha ya está completamente pagada.']);
        }

        // Validar que el monto no exceda el saldo pendiente
        if ($request->monto > $saldoPendiente) {
            return redirect()->back()->withErrors(['error' => 'El monto excede el saldo pendiente.']);
        }

        $planPago = $request->plan_pago;
        $monto = $request->monto;

        try {
            // Generar ID de transacción único (enviado a PagoFácil como paymentNumber)
            $companyTransactionIdGenerado = 'FICHA-' . $ficha->id . '-' . time();

            Log::info('🔑 [PagoFácil] ID de transacción generado', ['company_transaction_id' => $companyTransactionIdGenerado]);

            // Preparar datos para PagoFácil
            $cliente = $ficha->cliente->usuario->persona;
            $descripcionPago = match($planPago) {
                'TOTAL' => 'Pago Total',
                'ANTICIPO' => 'Anticipo (50%)',
                'SALDO' => 'Saldo Restante',
                default => 'Pago',
            };

            $qrData = [
                'paymentMethod' => 34, // QR habilitado para el comercio
                'clientName' => $cliente->nombre_completo ?? 'Cliente',
                'documentType' => 1, // CI
                'documentId' => $cliente->dni ?? '00000000',
                'phoneNumber' => $cliente->telefono ?? '70000000',
                'email' => $ficha->cliente->usuario->email ?? '',
                'paymentNumber' => $companyTransactionIdGenerado,
                'amount' => (float) $monto,
                'currency' => 2, // BOB
                'clientCode' => (string) $ficha->cliente_id,
                'callbackUrl' => config('app.url') . '/cliente/pagos/callback',
                'orderDetail' => [
                    [
                        'serial' => 1,
                        'product' => "Ficha #{$ficha->id} - {$ficha->servicio->nombre} ({$descripcionPago})",
                        'quantity' => 1,
                        'price' => (float) $monto,
                        'discount' => 0,
                        'total' => (float) $monto,
                    ]
                ]
            ];

            Log::info('📋 [PagoFácil] Datos preparados', ['qr_data' => $qrData]);

            // Generar QR
            Log::info('🚀 [PagoFácil] Llamando a generateQr...');
            $response = $this->pagoFacilService->generateQr($qrData);

            Log::info('✅ [PagoFácil] QR generado exitosamente', [
                'response' => $response,
                'tiene_transactionId' => isset($response['transactionId']),
                'tiene_qrBase64' => isset($response['qrBase64']),
                'tiene_expirationDate' => isset($response['expirationDate']),
            ]);

            // ✅ CRÍTICO (Opción A): usar el companyTransactionId devuelto por la API (si viene)
            // Es el que llega como PedidoID en los callbacks, así evitamos "Pago no encontrado".
            $companyTransactionIdFinal = $response['companyTransactionId'] ?? $companyTransactionIdGenerado;

            Log::info('🧾 [PagoFácil] company_transaction_id final', [
                'generado' => $companyTransactionIdGenerado,
                'devuelto_por_api' => $response['companyTransactionId'] ?? null,
                'final' => $companyTransactionIdFinal,
            ]);

            // Guardar el pago pendiente con el QR
            $pagoId = Str::uuid()->toString();
            $pago = Pago::create([
                'id' => $pagoId,
                'ficha_id' => $ficha->id,
                'monto' => $monto,
                'tipo' => 'CONTADO',
                'concepto' => $planPago,
                'metodo_pago' => 'QR',
                'fecha_pago' => now(),
                'estado' => 'PENDIENTE',
                'pagofacil_transaction_id' => $response['transactionId'],
                'company_transaction_id' => $companyTransactionIdFinal,
                'qr_base64' => $response['qrBase64'],
                'qr_status' => 'PENDING',
                'qr_expiration' => $response['expirationDate'] ? \Carbon\Carbon::parse($response['expirationDate']) : null,
            ]);

            Log::info('💾 [PagoFácil] Pago guardado', ['pago_id' => $pago->id]);

            $qrDataParaFlash = [
                'qrBase64' => $response['qrBase64'] ?? null,
                'transactionId' => $response['transactionId'] ?? null,
                'expirationDate' => $response['expirationDate'] ?? null,
                'pagoId' => $pago->id,  // ✅ CRÍTICO: Agregar pago_id para consultas
                // Solo para verificación/depuración en navegador
                'companyTransactionId' => $companyTransactionIdFinal,
            ];

            Log::info('📤 [PagoFácil] Redirigiendo con datos QR', ['qr_data' => $qrDataParaFlash]);

            // Guardar en sesión flash para que Inertia lo capture
            session()->flash('success', 'QR generado exitosamente.');
            session()->flash('qr_data', $qrDataParaFlash);

            // Redirigir a la página de procesar pago
            return redirect()->route('cliente.pagos.procesar', $ficha->id);

        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al generar QR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors(['error' => 'Error al generar QR: ' . $e->getMessage()]);
        }
    }

    /**
     * Registrar pago en efectivo (NUEVO)
     */
    public function registrarPagoEfectivo(Request $request)
    {
        $request->validate([
            'ficha_id' => 'required|string|exists:fichas,id',
            'plan_pago' => 'required|string|in:TOTAL,ANTICIPO,SALDO',
            'monto' => 'required|numeric|min:0.01',
        ]);

        $ficha = Ficha::with(['servicio', 'cliente.usuario.persona'])->findOrFail($request->ficha_id);

        // Verificar que la ficha pertenece al usuario autenticado
        if ($ficha->cliente_id !== auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'No tienes permiso para esta ficha.']);
        }

        $saldoPendiente = $ficha->calcularSaldoPendiente();

        if ($saldoPendiente <= 0) {
            return redirect()->back()->withErrors(['error' => 'Esta ficha ya está completamente pagada.']);
        }

        // Validar que el monto no exceda el saldo pendiente
        if ($request->monto > $saldoPendiente) {
            return redirect()->back()->withErrors(['error' => 'El monto excede el saldo pendiente.']);
        }

        try {
            // Registrar el pago en efectivo
            $pagoId = Str::uuid()->toString();
            $pago = Pago::create([
                'id' => $pagoId,
                'ficha_id' => $ficha->id,
                'monto' => $request->monto,
                'tipo' => 'CONTADO',
                'concepto' => $request->plan_pago,
                'metodo_pago' => 'EFECTIVO',
                'fecha_pago' => now(),
                'estado' => 'PAGADO',
            ]);

            Log::info('💵 [Pago Efectivo] Pago registrado', [
                'pago_id' => $pago->id,
                'ficha_id' => $ficha->id,
                'monto' => $pago->monto,
                'concepto' => $pago->concepto,
            ]);

            // Actualizar estado de la ficha
            $ficha->refresh();
            $ficha->actualizarEstadoPorPago();

            Log::info('✅ [Pago Efectivo] Ficha actualizada', [
                'ficha_id' => $ficha->id,
                'nuevo_estado' => $ficha->estado,
                'porcentaje_pagado' => $ficha->calcularPorcentajePagado(),
            ]);

            return redirect()->route('cliente.fichas.index')
                ->with('success', 'Pago en efectivo registrado exitosamente.');

        } catch (\Exception $e) {
            Log::error('❌ [Pago Efectivo] Error al registrar pago', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->withErrors(['error' => 'Error al registrar pago: ' . $e->getMessage()]);
        }
    }

    /**
     * Procesar pago con tarjeta (DESHABILITADO TEMPORALMENTE)
     */
    public function procesarTarjeta(Request $request)
    {
        $request->validate([
            'ficha_id' => 'required|string|exists:fichas,id',
            'numero_tarjeta' => 'required|string|min:13|max:19',
            'nombre_titular' => 'required|string|max:100',
            'fecha_vencimiento' => 'required|string|regex:/^\d{2}\/\d{2}$/',
            'cvv' => 'required|string|min:3|max:4',
        ]);

        $ficha = Ficha::with(['servicio', 'cliente.usuario.persona'])->findOrFail($request->ficha_id);

        // Verificar que la ficha pertenece al usuario autenticado
        if ($ficha->cliente_id !== auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'No tienes permiso para esta ficha.']);
        }

        // Calcular saldo pendiente
        $totalPagado = $ficha->pagos()->where('estado', 'PAGADO')->sum('monto');
        $totalAPagar = $ficha->servicio->costo ?? 0;
        $saldoPendiente = $totalAPagar - $totalPagado;

        if ($saldoPendiente <= 0) {
            return redirect()->back()->withErrors(['error' => 'Esta ficha ya está completamente pagada.']);
        }

        try {
            $companyTransactionId = 'FICHA-' . $ficha->id . '-' . time();

            // Preparar datos para PagoFácil
            $cliente = $ficha->cliente->usuario->persona;
            $tarjetaData = [
                'paymentMethod' => 1, // Tarjeta
                'cardNumber' => $request->numero_tarjeta,
                'cardHolderName' => $request->nombre_titular,
                'expirationDate' => $request->fecha_vencimiento,
                'cvv' => $request->cvv,
                'amount' => (float) $saldoPendiente,
                'currency' => 2, // BOB
                'paymentNumber' => $companyTransactionId,
                'clientName' => $cliente->nombre_completo ?? 'Cliente',
                'clientCode' => (string) $ficha->cliente_id,
            ];

            Log::info('💳 [PagoFácil] Procesando tarjeta', ['datos' => array_merge($tarjetaData, ['cardNumber' => '****', 'cvv' => '***'])]);

            // Procesar pago con tarjeta
            $response = $this->pagoFacilService->procesarTarjeta($tarjetaData);

            Log::info('✅ [PagoFácil] Tarjeta procesada', ['response' => $response]);

            // Guardar el pago
            $pagoId = Str::uuid()->toString();
            $pago = Pago::create([
                'id' => $pagoId,
                'ficha_id' => $ficha->id,
                'monto' => $saldoPendiente,
                'tipo' => 'CONTADO',
                'metodo_pago' => 'TARJETA',
                'fecha_pago' => now(),
                'estado' => isset($response['status']) && $response['status'] == 2 ? 'PAGADO' : 'PENDIENTE',
                'pagofacil_transaction_id' => $response['transactionId'] ?? null,
                'company_transaction_id' => $companyTransactionId,
            ]);

            // Actualizar estado de la ficha
            if ($pago->estado === 'PAGADO') {
                $ficha->refresh();
                $ficha->actualizarEstadoPorPago();
            }

            return redirect()->route('cliente.fichas.index')
                ->with('success', 'Pago procesado exitosamente.');

        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al procesar tarjeta', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->withErrors(['error' => 'Error al procesar pago: ' . $e->getMessage()]);
        }
    }

    /**
     * Callback de PagoFácil (Webhook)
     */
    public function callback(Request $request)
    {
        Log::info('📞 [PagoFácil] Callback recibido', [
            'all_data' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
        ]);

        // Capturar todos los campos según documentación oficial
        $pedidoId = $request->input('PedidoID') ?? $request->input('PedidoId') ?? $request->input('pedidoId') ?? $request->input('pedido_id');
        $estado = $request->input('Estado') ?? $request->input('estado') ?? $request->input('status') ?? $request->input('Status');
        $fecha = $request->input('Fecha') ?? $request->input('fecha');
        $hora = $request->input('Hora') ?? $request->input('hora');
        $metodoPago = $request->input('MetodoPago') ?? $request->input('metodoPago') ?? $request->input('metodo_pago');

        Log::info('🔍 [PagoFácil] Datos extraídos del callback (según documentación)', [
            'PedidoID' => $pedidoId,
            'Estado' => $estado,
            'Fecha' => $fecha,
            'Hora' => $hora,
            'MetodoPago' => $metodoPago,
            'todos_los_campos' => $request->all(),
        ]);

        // Validar campo obligatorio según documentación
        if (!$pedidoId) {
            Log::error('❌ [PagoFácil] No se recibió PedidoID en el callback', ['request_data' => $request->all()]);
            return response()->json(['error' => 1, 'message' => 'PedidoID no encontrado'], 400);
        }

        // Buscar el pago por company_transaction_id.
        // NOTA IMPORTANTE:
        // En la práctica, PagoFácil puede enviar un PedidoID recortado/truncado respecto al paymentNumber enviado.
        // Por eso, hacemos:
        // 1) match exacto
        // 2) match por prefijo (PedidoID%)
        // 3) fallback por contains (%PedidoID%) para casos raros
        $pago = Pago::where('company_transaction_id', $pedidoId)->first();
        if (!$pago) {
            $pago = Pago::where('company_transaction_id', 'like', $pedidoId . '%')
                ->orderByDesc('created_at')
                ->first();
        }
        if (!$pago) {
            $pago = Pago::where('company_transaction_id', 'like', '%' . $pedidoId . '%')
                ->orderByDesc('created_at')
                ->first();
        }

        if (!$pago) {
            Log::warning('⚠️ [PagoFácil] Pago no encontrado', [
                'company_transaction_id' => $pedidoId,
                'buscando_en' => 'company_transaction_id (exacto/prefijo/contains)'
            ]);
            return response()->json(['error' => 1, 'message' => 'Pago no encontrado'], 404);
        }

        Log::info('📋 [PagoFácil] Pago encontrado', [
            'pago_id' => $pago->id,
            'estado_actual' => $pago->estado,
            'qr_status_actual' => $pago->qr_status,
            'ficha_id' => $pago->ficha_id,
        ]);

        // Actualizar el estado del pago si fue exitoso
        // Verificar múltiples formatos de estado que PagoFácil podría enviar
        $estadoNormalizado = is_numeric($estado) ? (int)$estado : strtoupper(trim((string)$estado));
        $esPagado = in_array($estadoNormalizado, ['COMPLETADO', 'PAID', 'PAGADO', 2, '2', 'COMPLETED']) 
                 || $estado === 'Completado' 
                 || $estado === 'PAID';

        Log::info('💳 [PagoFácil] Verificando estado del pago', [
            'estado_recibido' => $estado,
            'estado_normalizado' => $estadoNormalizado,
            'es_pagado' => $esPagado,
        ]);

        if ($esPagado) {
            Log::info('✅ [PagoFácil] ¡PAGO CONFIRMADO EN CALLBACK! Actualizando...', [
                'pago_id' => $pago->id,
                'estado_anterior' => $pago->estado,
                'qr_status_anterior' => $pago->qr_status,
                'fecha_callback' => $fecha,
                'hora_callback' => $hora,
                'metodo_pago_callback' => $metodoPago,
            ]);

            // Construir fecha_pago usando Fecha y Hora del callback (según documentación)
            $fechaPago = null;
            if ($fecha && $hora) {
                try {
                    // Intentar parsear fecha y hora del callback
                    // Formato esperado: "Y-m-d" para fecha y "H:i:s" para hora
                    $fechaPago = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $fecha . ' ' . $hora);
                    Log::info('📅 [PagoFácil] Fecha y hora parseadas del callback', [
                        'fecha_original' => $fecha,
                        'hora_original' => $hora,
                        'fecha_pago_parseada' => $fechaPago->toDateTimeString(),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('⚠️ [PagoFácil] Error al parsear fecha/hora del callback, usando now()', [
                        'error' => $e->getMessage(),
                        'fecha' => $fecha,
                        'hora' => $hora,
                    ]);
                    $fechaPago = now();
                }
            } else {
                // Si no vienen fecha/hora del callback, usar now()
                Log::info('ℹ️ [PagoFácil] No se recibieron Fecha/Hora del callback, usando now()');
                $fechaPago = now();
            }

            $pago->update([
                'qr_status' => 'PAID',
                'estado' => 'PAGADO',
                'fecha_pago' => $fechaPago,
            ]);

            // Refrescar para obtener los nuevos valores
            $pago->refresh();

            Log::info('💾 [PagoFácil] Pago actualizado en BD', [
                'pago_id' => $pago->id,
                'nuevo_estado' => $pago->estado,
                'nuevo_qr_status' => $pago->qr_status,
                'nueva_fecha_pago' => $pago->fecha_pago,
                'fecha_usada_del_callback' => $fechaPago->toDateTimeString(),
                'metodo_pago_recibido' => $metodoPago,
            ]);

            // Actualizar estado de la ficha según el pago realizado
            if ($pago->ficha) {
                $pago->ficha->refresh();
                $pago->ficha->actualizarEstadoPorPago();

                Log::info('✅ [PagoFácil] Ficha actualizada', [
                    'ficha_id' => $pago->ficha_id,
                    'nuevo_estado' => $pago->ficha->estado,
                    'porcentaje_pagado' => $pago->ficha->calcularPorcentajePagado(),
                ]);
            }

            Log::info('✅ [PagoFácil] Callback procesado exitosamente', ['pago_id' => $pago->id]);
        } else {
            Log::info('ℹ️ [PagoFácil] Estado recibido no es de pago completado', [
                'estado' => $estado,
                'pago_id' => $pago->id,
                'fecha_callback' => $fecha,
                'hora_callback' => $hora,
                'metodo_pago_callback' => $metodoPago,
            ]);
        }

        // Responder conforme a la API de PagoFácil
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message' => 'Notificación recibida correctamente',
            'values' => true
        ], 200);
    }

    /**
     * Obtener estado de un pago por su ID (NUEVO - según código que funciona)
     */
    public function obtenerEstadoPorId($id)
    {
        try {
            $pago = Pago::with(['ficha.servicio', 'ficha.medico.usuario.persona'])->findOrFail($id);

            // Verificar que el pago pertenece al usuario autenticado
            if ($pago->ficha && $pago->ficha->cliente_id !== auth()->id()) {
                return response()->json(['error' => 'No tienes permiso'], 403);
            }

            // ✅ Consultar PagoFácil con timeout corto y actualizar BD con lo que venga (sin provocar 500)
            // Si el proveedor está lento/falla, devolvemos el estado actual de la BD.
            try {
                if ($pago->pagofacil_transaction_id && $pago->estado !== 'PAGADO') {
                    $result = $this->pagoFacilService->consultarTransaccionRapida($pago->pagofacil_transaction_id);

                    $values = $result['values'] ?? [];
                    $paymentStatus = $values['paymentStatus'] ?? null;
                    $paymentStatusDescription = $values['paymentStatusDescription'] ?? '';

                    $statusInt = $paymentStatus === null ? null : (int) $paymentStatus;
                    $descripcionNormalizada = strtoupper(trim((string) $paymentStatusDescription));

                    // Actualizar el "qr_status" local según lo recibido
                    // Reglas:
                    // - 5 o "PAID/APROBADO/COMPLETADO" => PAGADO
                    // - 2 o "REVISION" => (por requerimiento) confirmar como PAGADO (aunque el proveedor no llegue a 5)
                    // - 3 => ANULADO
                    // - 4 => EXPIRED
                    // - resto => PENDING
                    $esPagado = ($statusInt !== null && $statusInt === 5)
                        || in_array($descripcionNormalizada, ['PAID', 'PAGADO', 'COMPLETADO', 'COMPLETED', 'APROBADO'], true);

                    $esRevision = ($statusInt !== null && $statusInt === 2)
                        || str_contains($descripcionNormalizada, 'REVISION')
                        || str_contains($descripcionNormalizada, 'REVISIÓN');

                    if ($esPagado || $esRevision) {
                        $updateData = [
                            'qr_status' => 'PAID',
                            'estado' => 'PAGADO',
                            'fecha_pago' => now(),
                        ];

                        if (!$pago->concepto && $pago->ficha) {
                            $tienePagoAnticipo = $pago->ficha->tienePagoAnticipo();
                            $updateData['concepto'] = $tienePagoAnticipo ? 'SALDO' : 'ANTICIPO';
                        }

                        $pago->update($updateData);

                        if ($pago->ficha) {
                            $pago->ficha->refresh();
                            $pago->ficha->actualizarEstadoPorPago();
                        }

                        Log::info('✅ [PagoFácil] Pago actualizado desde /estado', [
                            'pago_id' => $pago->id,
                            'status_recibido' => $paymentStatus,
                            'status_description' => $paymentStatusDescription,
                            'nuevo_estado' => $pago->estado,
                            'nuevo_qr_status' => $pago->qr_status,
                        ]);
                    } elseif ($statusInt === 3) {
                        $pago->update(['qr_status' => 'CANCELLED', 'estado' => 'ANULADO']);
                    } elseif ($statusInt === 4) {
                        $pago->update(['qr_status' => 'EXPIRED']);
                    } else {
                        // Mantener PENDIENTE pero reflejar "PENDING" explícito
                        if ($pago->qr_status !== 'PENDING' || $pago->estado !== 'PENDIENTE') {
                            $pago->update(['qr_status' => 'PENDING', 'estado' => 'PENDIENTE']);
                        }
                    }

                    // Refrescar para responder con el último estado de BD
                    $pago->refresh();
                }
            } catch (\Exception $e) {
                Log::warning('⚠️ [PagoFácil] Consulta rápida falló, devolviendo estado BD', [
                    'pago_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }

            // ✅ SIEMPRE retornar el estado de la BD (aunque PagoFácil falle)
            return response()->json([
                'success' => true,
                'pago' => [
                    'id' => $pago->id,
                    'estado' => $pago->estado,
                    'qr_status' => $pago->qr_status,
                    'fecha_pago' => $pago->fecha_pago,
                    'monto' => $pago->monto,
                    'concepto' => $pago->concepto,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo estado de pago por ID', [
                'pago_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estado del pago'
            ], 500);
        }
    }

    /**
     * Consultar estado de transacción (MÉTODO ANTIGUO - mantener por compatibilidad)
     */
    public function consultarEstado(Request $request)
    {
        // Aceptar transactionId o ficha_id para compatibilidad
        $transactionId = $request->input('transaction_id') ?? $request->input('transactionId');
        $fichaId = $request->input('ficha_id');
        
        if (!$transactionId ) {
            return response()->json(['error' => 'Se requiere transaction_id'], 400);
        }

        // Buscar pago por transactionId directamente (como en Postman)
        if ($transactionId) {
            $pago = Pago::where('pagofacil_transaction_id', $transactionId)
                ->first();
            
            if (!$pago) {
                Log::warning('⚠️ [PagoFácil] No se encontró pago con transactionId', ['transactionId' => $transactionId]);
                return response()->json(['error' => 'Pago no encontrado'], 404);
            }
        }

        // Verificar que el pago pertenece al usuario autenticado
        if ($pago->ficha && $pago->ficha->cliente_id !== auth()->id()) {
            return response()->json(['error' => 'No tienes permiso'], 403);
        }

        // REFRESCAR el modelo para obtener los datos más recientes de la BD
        // Esto es importante porque el callback puede haber actualizado el pago
        $pago->refresh();

        Log::info('🔍 [PagoFácil] Estado actual del pago en BD', [
            'pago_id' => $pago->id,
            'qr_status' => $pago->qr_status,
            'estado' => $pago->estado,
            'pagofacil_transaction_id' => $pago->pagofacil_transaction_id,
            'company_transaction_id' => $pago->company_transaction_id,
            'updated_at' => $pago->updated_at,
        ]);

        // Si el pago ya está PAID, devolver directamente sin consultar API
        if ($pago->qr_status === 'PAID' || $pago->estado === 'PAGADO') {
            Log::info('✅ [PagoFácil] ¡PAGO YA CONFIRMADO EN BD! Devolviendo directamente...', [
                'pago_id' => $pago->id,
                'qr_status' => $pago->qr_status,
                'estado' => $pago->estado,
                'fecha_pago' => $pago->fecha_pago,
            ]);

            return response()->json([
                'success' => true,
                'status' => 'PAID',
                'message' => '✅ Pago confirmado exitosamente',
            ]);
        }

        try {
            Log::info('🔍 [PagoFácil] Consultando estado de transacción', [
                'pagofacil_transaction_id' => $pago->pagofacil_transaction_id,
                'company_transaction_id' => $pago->company_transaction_id,
                'pago_id' => $pago->id,
                'qr_status_actual' => $pago->qr_status,
                'estado_actual' => $pago->estado,
                'ficha_id' => $pago->ficha_id
            ]);

            // Consultar usando el ID de PagoFácil o el ID de la empresa
            $result = $this->pagoFacilService->consultarTransaccion(
                $pago->pagofacil_transaction_id,
                $pago->company_transaction_id
            );

            Log::info('📥 [PagoFácil] Respuesta de consulta recibida', [
                'result' => $result,
                'result_keys' => array_keys($result ?? []),
                'tiene_values' => isset($result['values']),
                'error' => $result['error'] ?? null,
                'status_api' => $result['status'] ?? null,
                'message' => $result['message'] ?? null,
            ]);

            // ✅ Validar estructura de respuesta según código que funciona
            $errorCode = $result['error'] ?? null;
            $apiStatus = $result['status'] ?? null;
            
            // Validar errores lógicos de la API
            if ($errorCode !== 0 && $errorCode !== null) {
                Log::warning('⚠️ [PagoFácil] La API reportó un error', [
                    'error' => $errorCode,
                    'status' => $apiStatus,
                    'message' => $result['message'] ?? null
                ]);
                return response()->json([
                    'success' => false,
                    'status' => 'ERROR',
                    'message' => $result['message'] ?? 'Error en la transacción',
                ], 400);
            }

            // La información del pago está en 'values'
            $responseData = $result['values'] ?? [];
            
            if (empty($responseData)) {
                Log::warning('⚠️ [PagoFácil] No se encontró "values" en la respuesta', ['result' => $result]);
                return response()->json([
                    'success' => false,
                    'status' => 'ERROR',
                    'message' => 'Datos no encontrados',
                ], 404);
            }

            // ✅ Obtener datos usando null coalescing operator
            $paymentStatus = $responseData['paymentStatus'] ?? null;
            $paymentStatusDescription = $responseData['paymentStatusDescription'] ?? '';

            Log::info('🔍 [PagoFácil] Analizando respuesta', [
                'error_code' => $errorCode,
                'api_status' => $apiStatus,
                'paymentStatus' => $paymentStatus,
                'paymentStatusDescription' => $paymentStatusDescription,
                'pagofacilTransactionId' => $responseData['pagofacilTransactionId'] ?? null,
                'companyTransactionId' => $responseData['companyTransactionId'] ?? null,
                'paymentDate' => $responseData['paymentDate'] ?? null,
                'paymentTime' => $responseData['paymentTime'] ?? null,
            ]);

            // Verificar que tenemos el paymentStatus
            if ($paymentStatus === null) {
                Log::warning('⚠️ [PagoFácil] No se encontró paymentStatus en la respuesta', [
                    'responseData' => $responseData
                ]);
                return response()->json([
                    'success' => false,
                    'status' => 'ERROR',
                    'message' => 'No se pudo determinar el estado del pago',
                ], 500);
            }

            // ✅ CRÍTICO: Convertir a entero
            $status = (int)$paymentStatus;
            
            Log::info('💳 [PagoFácil] Estado detectado', [
                'status' => $status,
                'status_type' => gettype($status),
                'status_description' => $paymentStatusDescription,
                // ✅ Estados según código que funciona: 1=EN PROCESO, 2=REVISION, 3=CANCELLED, 4=EXPIRED, 5=PAID
            ]);
            
            // ✅ CRÍTICO: Verificar si está "confirmado" para nuestro sistema
            // Nota: Por requerimiento, consideramos 2 (Revisión) como confirmado (PAID/PAGADO). 1 (En Proceso) NO confirma.
            $descripcionNormalizada = strtoupper(trim((string) $paymentStatusDescription));
            $esPagado = in_array($status, [2, 5], true)
                || in_array($descripcionNormalizada, [
                    'PAID',
                    'PAGADO',
                    'COMPLETADO',
                    'COMPLETED',
                    'APROBADO',
                    'REVISION',
                    'REVISIÓN',
                    'EN REVISION',
                    'EN REVISIÓN',
                ], true);
            
            if ($esPagado) { // ✅ PAID (estado 5)
                Log::info('✅ [PagoFácil] ¡PAGO CONFIRMADO! Actualizando estado...', [
                    'pago_id' => $pago->id,
                    'status_recibido' => $status,
                    'status_description' => $paymentStatusDescription
                ]);

                // ✅ Actualizar pago con concepto si no lo tiene
                $updateData = [
                    'qr_status' => 'PAID',
                    'estado' => 'PAGADO',
                    'fecha_pago' => now(),
                ];

                // Si no tiene concepto, determinarlo basado en los pagos previos
                if (!$pago->concepto) {
                    $tienePagoAnticipo = $pago->ficha->tienePagoAnticipo();
                    $updateData['concepto'] = $tienePagoAnticipo ? 'SALDO' : 'ANTICIPO';
                }

                $pago->update($updateData);

                Log::info('💾 [PagoFácil] Pago actualizado en BD', [
                    'pago_id' => $pago->id,
                    'nuevo_qr_status' => $pago->qr_status,
                    'nuevo_estado' => $pago->estado,
                    'concepto' => $pago->concepto
                ]);

                // ✅ Actualizar ficha
                if ($pago->ficha) {
                    $pago->ficha->refresh();
                    $pago->ficha->actualizarEstadoPorPago();

                    Log::info('✅ [PagoFácil] Ficha actualizada', [
                        'ficha_id' => $pago->ficha_id,
                        'nuevo_estado' => $pago->ficha->estado,
                        'porcentaje_pagado' => $pago->ficha->calcularPorcentajePagado(),
                        'total_pagado' => $pago->ficha->calcularTotalPagado(),
                    ]);
                }

                Log::info('📤 [PagoFácil] Enviando respuesta PAID al frontend');

                return response()->json([
                    'success' => true,
                    'status' => 'PAID',
                    'message' => '✅ Pago confirmado exitosamente',
                ]);
            } elseif ($status == 3) { // CANCELLED
                $pago->update(['qr_status' => 'CANCELLED', 'estado' => 'ANULADO']);
                return response()->json([
                    'success' => false,
                    'status' => 'CANCELLED',
                    'message' => '❌ Pago cancelado',
                ]);
            } elseif ($status == 4) { // EXPIRED
                $pago->update(['qr_status' => 'EXPIRED']);
                return response()->json([
                    'success' => false,
                    'status' => 'EXPIRED',
                    'message' => '⏰ QR expirado',
                ]);
            } else {
                // Estado desconocido
                Log::warning('⚠️ [PagoFácil] Estado desconocido', [
                    'status' => $status,
                    'status_description' => $paymentStatusDescription
                ]);
                return response()->json([
                    'success' => false,
                    'status' => 'UNKNOWN',
                    'message' => '⏳ Estado desconocido: ' . ($paymentStatusDescription ?? $status),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al consultar estado', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Si el error es que no se encontró el endpoint, devolver un mensaje más amigable
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                return response()->json([
                    'success' => false,
                    'status' => 'PENDING',
                    'message' => '⏳ Consulta de estado no disponible. El pago se confirmará automáticamente cuando se complete.',
                ], 200); // 200 para que no se muestre como error en el frontend
            }

            return response()->json([
                'success' => false,
                'status' => 'ERROR',
                'message' => 'Error al consultar estado: ' . $e->getMessage()
            ], 500);
        }
    }
}
