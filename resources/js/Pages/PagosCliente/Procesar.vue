<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    ficha: Object,
    costoTotal: Number,
    totalPagado: Number,
    saldoPendiente: Number,
    porcentajePagado: Number,
    tipoPagoRequerido: String,
    pagoPendiente: Object,
});

const pagina = usePage();
const metodoSeleccionado = ref(null);
const qrData = ref(null);
const pagoId = ref(null);  // ✅ NUEVO: Guardar ID de pago para consultas
const consultandoEstado = ref(false);
const intervaloConsulta = ref(null);
const mostrandoQR = ref(false);

// Obtener parámetros de la URL (plan y monto)
const urlParams = new URLSearchParams(window.location.search);
const planSeleccionado = ref(urlParams.get('plan') || props.tipoPagoRequerido);
const montoAPagar = ref(parseFloat(urlParams.get('monto')) || props.saldoPendiente);

// Formulario para pago efectivo
const formularioEfectivo = useForm({
    ficha_id: props.ficha.id,
    plan_pago: planSeleccionado.value,
    monto: montoAPagar.value,
});

// Título del tipo de pago
const tituloPago = computed(() => {
    if (planSeleccionado.value === 'TOTAL') return '💰 Pago Total';
    if (planSeleccionado.value === 'ANTICIPO') return '📊 Pago de Anticipo';
    if (planSeleccionado.value === 'SALDO') return '💵 Pago de Saldo';
    return '💳 Realizar Pago';
});

// Descripción del pago
const descripcionPago = computed(() => {
    if (planSeleccionado.value === 'TOTAL') {
        return 'Estás pagando el costo total del servicio de una vez. ¡Gracias por tu preferencia!';
    }
    if (planSeleccionado.value === 'ANTICIPO') {
        return 'Este pago confirmará tu ficha. El saldo restante se pagará el día de tu cita.';
    }
    if (planSeleccionado.value === 'SALDO') {
        return 'Este es el pago final de tu ficha. Después de completar este pago, tu ficha estará totalmente pagada.';
    }
    return '';
});

function formatearMoneda(valor) {
    if (!valor) return 'Bs. 0.00';
    return `Bs. ${Number(valor).toFixed(2)}`;
}

function formatearFecha(fecha) {
    if (!fecha) return '';
    return new Date(fecha).toLocaleDateString('es-BO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatearHora(hora) {
    if (!hora) return '';
    if (typeof hora === 'string') {
        // Si viene con T (formato ISO)
        if (hora.includes('T')) {
            const [, timePart] = hora.split('T');
            if (timePart) return timePart.slice(0, 5);
        }
        // Si viene como "HH:MM:SS" simple
        if (hora.includes(':')) {
            const partes = hora.split(':');
            return `${partes[0]}:${partes[1]}`;
        }
    }
    return hora;
}

function seleccionarMetodo(metodo) {
    metodoSeleccionado.value = metodo;
    if (metodo === 'QR') {
        generarQr();
    }
}

function generarQr() {
    console.log('🔄 Generando QR...');
    mostrandoQR.value = true;
    
    router.post(route('cliente.pagos.generar-qr'), {
        ficha_id: props.ficha.id,
        plan_pago: planSeleccionado.value,
        monto: montoAPagar.value,
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('✅ QR generado', page.props.flash);
            const qrDataRecibido = page.props.flash?.qr_data || page.props.qr_data || null;
            
            if (qrDataRecibido) {
                qrData.value = qrDataRecibido;
                // ✅ IMPORTANTE: setear pagoId inmediatamente (sin depender de recargar la página)
                pagoId.value = qrDataRecibido.pagoId || qrDataRecibido.pago_id || null;
                iniciarConsultaAutomatica();
            }
        },
        onError: (errors) => {
            console.error('❌ Error al generar QR:', errors);
            mostrandoQR.value = false;
        },
    });
}

function iniciarConsultaAutomatica() {
    if (intervaloConsulta.value) {
        clearInterval(intervaloConsulta.value);
    }

    intervaloConsulta.value = setInterval(() => {
        consultarEstadoPago();
    }, 5000);
}

async function consultarEstadoPago() {
    // ✅ NUEVO: Usar pagoId en lugar de transactionId
    if (!pagoId.value || consultandoEstado.value) return;

    consultandoEstado.value = true;

    try {
        // ✅ NUEVO: Consultar por ID de pago (más confiable)
        const response = await axios.get(route('cliente.pagos.estado-por-id', pagoId.value));

        console.log('📥 Estado del pago:', response.data);

        if (response.data.success) {
            const estado = response.data.pago.estado;
            
            // ✅ Verificar si está PAGADO (según BD, no API)
            if (estado === 'PAGADO') {
                console.log('✅ ¡Pago confirmado!');
                clearInterval(intervaloConsulta.value);
                
                setTimeout(() => {
                    router.visit(route('cliente.fichas.index'), {
                        onSuccess: () => {
                            alert('✅ ¡Pago confirmado exitosamente!');
                        }
                    });
                }, 2000);
            } else {
                console.log('⏳ Pago aún pendiente:', estado);
            }
        }

    } catch (error) {
        console.error('❌ Error al consultar estado:', error);
        // ✅ NO detener consultas si hay error
    } finally {
        consultandoEstado.value = false;
    }
}

function procesarPagoEfectivo() {
    formularioEfectivo.post(route('cliente.pagos.efectivo'), {
        preserveScroll: true,
        onSuccess: () => {
            alert('✅ Pago en efectivo registrado exitosamente.');
            router.visit(route('cliente.fichas.index'));
        },
    });
}

onMounted(() => {
    const qrDataFlash = pagina.props.flash?.qr_data;
    if (qrDataFlash) {
        qrData.value = qrDataFlash;
        pagoId.value = qrDataFlash.pagoId;  // ✅ NUEVO: Guardar pagoId
        metodoSeleccionado.value = 'QR';
        mostrandoQR.value = true;
        
        console.log('✅ QR generado', {
            pagoId: pagoId.value,
            transactionId: qrDataFlash.transactionId,
            expirationDate: qrDataFlash.expirationDate
        });
        
        iniciarConsultaAutomatica();
    }
});

onUnmounted(() => {
    if (intervaloConsulta.value) {
        clearInterval(intervaloConsulta.value);
    }
});
</script>

<template>
    <AppLayout :title="tituloPago">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ tituloPago }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Información de la Ficha -->
                <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">📋 Resumen de tu Ficha</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Servicio:</p>
                            <p class="font-semibold text-gray-800">{{ ficha.servicio.nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Médico:</p>
                            <p class="font-semibold text-gray-800">Dr/a. {{ ficha.medico.usuario.persona.nombre }} {{ ficha.medico.usuario.persona.apellidos }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Fecha y Hora:</p>
                            <p class="font-semibold text-gray-800">{{ formatearFecha(ficha.fecha) }} - {{ formatearHora(ficha.hora) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Estado:</p>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold" 
                                  :class="{
                                      'bg-yellow-100 text-yellow-800': ficha.estado === 'PENDIENTE_PAGO',
                                      'bg-blue-100 text-blue-800': ficha.estado === 'ANTICIPO_PAGADO',
                                      'bg-green-100 text-green-800': ficha.estado === 'PAGADA_COMPLETA',
                                  }">
                                {{ ficha.estado.replace('_', ' ') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Información del Pago -->
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 shadow-md rounded-lg p-6 mb-6">
                    <h3 class="text-2xl font-bold text-blue-800 mb-2">{{ tituloPago }}</h3>
                    <p class="text-gray-700 mb-4">{{ descripcionPago }}</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-sm text-gray-600">Costo Total</p>
                            <p class="text-2xl font-bold text-gray-800">{{ formatearMoneda(costoTotal) }}</p>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-sm text-gray-600">Ya Pagaste</p>
                            <p class="text-2xl font-bold text-green-600">{{ formatearMoneda(totalPagado) }}</p>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-sm text-gray-600">Monto a Pagar Ahora</p>
                            <p class="text-3xl font-bold text-blue-600">{{ formatearMoneda(montoAPagar) }}</p>
                        </div>
                    </div>

                    <!-- Barra de Progreso -->
                    <div class="mt-4">
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span>Progreso de Pago</span>
                            <span>{{ Math.round(porcentajePagado) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-green-600 h-3 rounded-full transition-all" 
                                 :style="{ width: porcentajePagado + '%' }"></div>
                        </div>
                    </div>
                </div>

                <!-- Métodos de Pago -->
                <div v-if="!mostrandoQR" class="bg-white shadow-md rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">💳 Selecciona tu método de pago</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Pago con QR -->
                        <button @click="seleccionarMetodo('QR')"
                                class="p-6 border-2 rounded-lg hover:shadow-lg transition"
                                :class="metodoSeleccionado === 'QR' ? 'border-blue-600 bg-blue-50' : 'border-gray-300'">
                            <div class="text-center">
                                <div class="text-5xl mb-3">📱</div>
                                <h4 class="text-lg font-bold text-gray-800">Pago con QR</h4>
                                <p class="text-sm text-gray-600">Escanea con tu app bancaria</p>
                            </div>
                        </button>

                        <!-- Pago en Efectivo -->
                        <button @click="seleccionarMetodo('EFECTIVO')"
                                class="p-6 border-2 rounded-lg hover:shadow-lg transition"
                                :class="metodoSeleccionado === 'EFECTIVO' ? 'border-green-600 bg-green-50' : 'border-gray-300'">
                            <div class="text-center">
                                <div class="text-5xl mb-3">💵</div>
                                <h4 class="text-lg font-bold text-gray-800">Pago en Efectivo</h4>
                                <p class="text-sm text-gray-600">Pagar en recepción</p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Panel de QR -->
                <div v-if="mostrandoQR && qrData" class="bg-white shadow-md rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">📱 Escanea el código QR</h3>
                    
                    <div class="flex justify-center mb-4">
                        <img :src="'data:image/png;base64,' + qrData.qrBase64" 
                             alt="Código QR" 
                             class="border-4 border-blue-500 rounded-lg shadow-lg" 
                             style="max-width: 300px;">
                    </div>

                    <div class="text-center">
                        <p class="text-gray-700 mb-2">Monto a pagar: <span class="text-2xl font-bold text-blue-600">{{ formatearMoneda(montoAPagar) }}</span></p>
                        <p class="text-sm text-gray-600">Esperando confirmación de pago...</p>
                        
                        <div class="flex justify-center items-center mt-4">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            <span class="ml-3 text-gray-600">Consultando estado...</span>
                        </div>
                    </div>

                    <button @click="mostrandoQR = false; qrData = null"
                            class="mt-6 w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        ← Volver a métodos de pago
                    </button>
                </div>

                <!-- Panel de Efectivo -->
                <div v-if="metodoSeleccionado === 'EFECTIVO' && !mostrandoQR" class="bg-white shadow-md rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">💵 Instrucciones para Pago en Efectivo</h3>
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4">
                        <p class="text-yellow-800">
                            <strong>Importante:</strong> Debes acercarte a la recepción del consultorio para realizar el pago en efectivo.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <p>✅ Monto a pagar: <strong class="text-2xl text-green-600">{{ formatearMoneda(montoAPagar) }}</strong></p>
                        <p>✅ Solicita tu comprobante de pago</p>
                        <p>✅ Tu ficha se confirmará inmediatamente</p>
                    </div>

                    <div class="mt-6 flex space-x-4">
                        <button @click="metodoSeleccionado = null"
                                class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                            ← Cancelar
                        </button>
                        <button @click="procesarPagoEfectivo"
                                :disabled="formularioEfectivo.processing"
                                class="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:bg-gray-400">
                            {{ formularioEfectivo.processing ? 'Procesando...' : 'Confirmar Pago en Efectivo' }}
                        </button>
                    </div>
                </div>

                <!-- Botón Volver -->
                <div class="text-center">
                    <button @click="$inertia.visit(route('cliente.fichas.index'))"
                            class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        ← Volver a Mis Fichas
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
