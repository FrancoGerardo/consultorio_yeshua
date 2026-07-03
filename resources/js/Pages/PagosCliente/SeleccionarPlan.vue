<template>
    <AppLayout title="Seleccionar Plan de Pago">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                💳 Seleccionar Plan de Pago
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
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
                            <p class="text-sm text-gray-600">Costo Total:</p>
                            <p class="font-semibold text-green-600 text-xl">Bs. {{ costoTotal.toFixed(2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Planes de Pago Disponibles -->
                <div class="space-y-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">🎯 Elige tu Plan de Pago</h3>

                    <!-- OPCIÓN A: Pago Total -->
                    <div v-if="opciones.pago_total.disponible" 
                         @click="seleccionarPlan('TOTAL', opciones.pago_total.monto_final)"
                         class="bg-gradient-to-r from-green-50 to-green-100 border-2 rounded-lg p-6 cursor-pointer hover:shadow-lg transition"
                         :class="planSeleccionado === 'TOTAL' ? 'border-green-600 shadow-lg' : 'border-green-300'">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="bg-green-500 text-white rounded-full w-12 h-12 flex items-center justify-center text-2xl">💰</div>
                                <div>
                                    <h4 class="text-xl font-bold text-green-800">Opción A: Pago Total</h4>
                                    <p class="text-sm text-gray-600">Paga todo ahora y obtén un descuento especial</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 line-through">Bs. {{ costoTotal.toFixed(2) }}</p>
                                <p class="text-3xl font-bold text-green-600">Bs. {{ opciones.pago_total.monto_final.toFixed(2) }}</p>
                                <p class="text-sm text-green-700">Descuento: {{ opciones.pago_total.porcentaje_descuento }}%</p>
                            </div>
                        </div>
                    </div>

                    <!-- OPCIÓN B: Anticipo + Saldo -->
                    <div @click="seleccionarPlan('ANTICIPO', opciones.anticipo_saldo.monto_anticipo)"
                         class="bg-gradient-to-r from-blue-50 to-blue-100 border-2 rounded-lg p-6 cursor-pointer hover:shadow-lg transition"
                         :class="planSeleccionado === 'ANTICIPO' ? 'border-blue-600 shadow-lg' : 'border-blue-300'">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="bg-blue-500 text-white rounded-full w-12 h-12 flex items-center justify-center text-2xl">📊</div>
                                <div>
                                    <h4 class="text-xl font-bold text-blue-800">Opción B: Anticipo + Saldo (Recomendado)</h4>
                                    <p class="text-sm text-gray-600">Paga {{ opciones.anticipo_saldo.porcentaje_anticipo }}% ahora y el resto el día de tu cita</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-blue-600">Anticipo: Bs. {{ opciones.anticipo_saldo.monto_anticipo.toFixed(2) }}</p>
                                <p class="text-sm text-gray-600">Saldo: Bs. {{ opciones.anticipo_saldo.monto_saldo.toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- OPCIÓN C: Plan de Cuotas -->
                    <div v-if="opciones.plan_cuotas.disponible"
                         @click="mostrarModalCuotas = true"
                         class="bg-gradient-to-r from-purple-50 to-purple-100 border-2 rounded-lg p-6 cursor-pointer hover:shadow-lg transition"
                         :class="planSeleccionado === 'CUOTA' ? 'border-purple-600 shadow-lg' : 'border-purple-300'">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="bg-purple-500 text-white rounded-full w-12 h-12 flex items-center justify-center text-2xl">📅</div>
                                <div>
                                    <h4 class="text-xl font-bold text-purple-800">Opción C: Plan de Cuotas</h4>
                                    <p class="text-sm text-gray-600">Paga en {{ opciones.plan_cuotas.max_cuotas }} cuotas mensuales</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-purple-600">Anticipo: Bs. {{ opciones.plan_cuotas.monto_anticipo.toFixed(2) }}</p>
                                <p class="text-sm text-gray-600">Resto: Bs. {{ opciones.plan_cuotas.monto_restante.toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="bg-gray-100 border-2 border-gray-300 rounded-lg p-6">
                        <div class="flex items-center space-x-4">
                            <div class="bg-gray-400 text-white rounded-full w-12 h-12 flex items-center justify-center text-2xl">📅</div>
                            <div>
                                <h4 class="text-xl font-bold text-gray-600">Opción C: Plan de Cuotas (No disponible)</h4>
                                <p class="text-sm text-gray-500">Solo para servicios con costo mayor a Bs. {{ opciones.plan_cuotas.monto_minimo_requerido }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón Continuar -->
                <div class="mt-8 flex justify-between items-center">
                    <button @click="$inertia.visit(route('cliente.fichas.index'))" 
                            class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        ← Cancelar
                    </button>
                    <button @click="continuarConPlan" 
                            :disabled="!planSeleccionado"
                            :class="planSeleccionado ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed'"
                            class="px-8 py-3 text-white rounded-lg transition font-semibold text-lg">
                        Continuar al Pago →
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    ficha: Object,
    costoTotal: Number,
    opciones: Object,
    configuracion: Object,
    contextoStaff: {
        type: Boolean,
        default: false,
    },
});

const planSeleccionado = ref(null);
const montoAPagar = ref(0);
const mostrarModalCuotas = ref(false);

const seleccionarPlan = (plan, monto) => {
    planSeleccionado.value = plan;
    montoAPagar.value = monto;
};

const continuarConPlan = () => {
    if (!planSeleccionado.value) return;

    const rutaProcesar = props.contextoStaff
        ? route('fichas.pago.procesar', props.ficha.id)
        : route('cliente.pagos.procesar', props.ficha.id);

    router.visit(rutaProcesar, {
        method: 'get',
        data: {
            plan: planSeleccionado.value,
            monto: montoAPagar.value,
        },
    });
};

const formatearFecha = (fecha) => {
    const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(fecha).toLocaleDateString('es-ES', opciones);
};

const formatearHora = (hora) => {
    // La hora viene como string "HH:MM:SS" de la BD
    if (typeof hora === 'string') {
        const partes = hora.split(':');
        return `${partes[0]}:${partes[1]}`;
    }
    return hora;
};
</script>

