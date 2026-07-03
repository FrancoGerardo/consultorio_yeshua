<template>
    <AppLayout title="Recepción">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🛎️ Recepción — Check-in de Pacientes
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Estadísticas -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="bg-white shadow rounded-lg p-4">
                        <p class="text-sm text-gray-600">Total del Día</p>
                        <p class="text-2xl font-bold text-gray-800">{{ estadisticas.total_dia }}</p>
                    </div>
                    <div class="bg-white shadow rounded-lg p-4">
                        <p class="text-sm text-gray-600">Por Llegar</p>
                        <p class="text-2xl font-bold text-blue-600">{{ estadisticas.programadas }}</p>
                    </div>
                    <div class="bg-white shadow rounded-lg p-4">
                        <p class="text-sm text-gray-600">En Sala de Espera</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ estadisticas.en_espera }}</p>
                    </div>
                    <div class="bg-white shadow rounded-lg p-4">
                        <p class="text-sm text-gray-600">En Atención</p>
                        <p class="text-2xl font-bold text-red-600">{{ estadisticas.en_atencion }}</p>
                    </div>
                    <div class="bg-white shadow rounded-lg p-4">
                        <p class="text-sm text-gray-600">Atendidas</p>
                        <p class="text-2xl font-bold text-green-600">{{ estadisticas.atendidas }}</p>
                    </div>
                </div>

                <!-- Citas por llegar -->
                <div class="bg-white shadow-xl rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-2">📅 Citas Programadas — Por llegar</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Registre la llegada cuando el paciente se presente en recepción.
                    </p>

                    <div v-if="programadas.length === 0" class="text-center py-8 text-gray-500">
                        No hay citas pendientes de check-in
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="ficha in programadas"
                            :key="ficha.id"
                            class="p-4 border-2 border-blue-200 bg-blue-50 rounded-lg flex items-center justify-between gap-4"
                        >
                            <div class="flex-1">
                                <p class="font-bold text-lg text-gray-900">
                                    {{ ficha.cliente?.usuario?.persona?.nombre_completo }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Hora cita: {{ formatearHora(ficha.hora) }} |
                                    Dr(a). {{ ficha.medico?.usuario?.persona?.nombre_completo }} |
                                    {{ ficha.servicio?.nombre }} |
                                    Sala: {{ ficha.sala?.numero || 'Sin asignar' }}
                                </p>
                                <p
                                    v-if="esAntesDeHoraCita(ficha.fecha, ficha.hora)"
                                    class="text-xs text-amber-700 mt-1"
                                >
                                    ⚠️ Llegada anticipada: la cita es a las {{ formatearHora(ficha.hora) }}
                                    (faltan ~{{ minutosAntesDeCita(ficha.fecha, ficha.hora) }} min)
                                </p>
                                <span class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-blue-600 text-white">
                                    {{ etiquetaProgramada(ficha.estado) }}
                                </span>
                            </div>
                            <button
                                type="button"
                                :disabled="procesando === ficha.id"
                                class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 whitespace-nowrap"
                                @click="registrarLlegada(ficha)"
                            >
                                {{ procesando === ficha.id ? 'Registrando...' : '✅ Registrar llegada' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ya en espera -->
                <div class="bg-white shadow-xl rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">⏳ En Sala de Espera</h3>

                    <div v-if="en_espera.length === 0" class="text-center py-8 text-gray-500">
                        Ningún paciente en sala de espera
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="ficha in en_espera"
                            :key="ficha.id"
                            class="p-4 border-2 border-yellow-400 bg-yellow-50 rounded-lg"
                        >
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-bold text-lg">{{ ficha.cliente?.usuario?.persona?.nombre_completo }}</p>
                                    <p class="text-sm text-gray-600">
                                        Cita: {{ formatearHora(ficha.hora) }} |
                                        Dr(a). {{ ficha.medico?.usuario?.persona?.nombre_completo }}
                                    </p>
                                    <p v-if="ficha.fecha_llegada" class="text-xs text-yellow-800 mt-1">
                                        Llegó: {{ formatearLlegada(ficha.fecha_llegada) }}
                                    </p>
                                    <p
                                        v-if="llegoAntesDeCita(ficha.fecha_llegada, ficha.fecha, ficha.hora)"
                                        class="text-xs text-amber-700 mt-1"
                                    >
                                        ⏰ Llegó antes de la hora de cita (programada {{ formatearHora(ficha.hora) }})
                                    </p>
                                </div>
                                <span class="px-3 py-1 bg-yellow-600 text-white rounded-full text-sm font-semibold">
                                    En Espera
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import { formatearHoraCita, minutosAntesDeCita, esAntesDeHoraCita, llegoAntesDeCita } from '@/utils/formatearHora';

defineProps({
    programadas: Array,
    en_espera: Array,
    estadisticas: Object,
});

const procesando = ref(null);

const formatearHora = formatearHoraCita;

const formatearLlegada = (fecha) => {
    if (!fecha) return '';
    return new Date(fecha).toLocaleTimeString('es-BO', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const etiquetaProgramada = (estado) => {
    const map = {
        PAGADA_COMPLETA: 'Pagada — Programada',
        ANTICIPO_PAGADO: 'Anticipo pagado',
        CONFIRMADA: 'Confirmada',
    };
    return map[estado] || estado;
};

const registrarLlegada = async (ficha) => {
    const minutosTemprano = minutosAntesDeCita(ficha.fecha, ficha.hora);

    if (minutosTemprano > 30) {
        const confirmar = window.confirm(
            `El paciente llegó más de 30 minutos antes de su cita.\n\n`
            + `Cita programada: ${formatearHora(ficha.hora)} (faltan ~${minutosTemprano} min)\n\n`
            + `¿Registrar llegada en sala de espera?`,
        );

        if (!confirmar) {
            return;
        }
    } else if (minutosTemprano > 0) {
        const confirmar = window.confirm(
            `Llegada anticipada: la cita es a las ${formatearHora(ficha.hora)}.\n\n`
            + `¿Registrar llegada?`,
        );

        if (!confirmar) {
            return;
        }
    }

    procesando.value = ficha.id;
    try {
        const response = await axios.post(route('recepcion.llegada', ficha.id));
        if (response.data.success) {
            router.reload({ only: ['programadas', 'en_espera', 'estadisticas'] });
        } else {
            alert(response.data.message || 'No se pudo registrar la llegada');
        }
    } catch (error) {
        alert(error.response?.data?.message || 'Error al registrar la llegada');
    } finally {
        procesando.value = null;
    }
};
</script>
