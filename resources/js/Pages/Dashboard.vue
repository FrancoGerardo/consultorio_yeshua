<template>
    <AppLayout :title="rol === 'CLIENTE' ? 'Inicio' : 'Dashboard'">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                👋 Bienvenido, {{ usuario.persona.nombres }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Fecha y Hora Actual -->
                <div class="mb-6 text-center">
                    <p class="text-2xl font-bold text-gray-800">{{ fechaHoraActual }}</p>
                    <p class="text-sm text-gray-600">{{ diaSemana }}</p>
                </div>

                <!-- TARJETAS DE MÉTRICAS GENERALES (Solo para Administrador, Médico, Secretaria) -->
                <div v-if="rol !== 'CLIENTE'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Pacientes Atendidos Hoy -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-90">Pacientes Atendidos Hoy</p>
                                <p class="text-4xl font-bold mt-2">{{ metricas.pacientes_atendidos_hoy }}</p>
                            </div>
                            <div class="text-5xl opacity-80">👥</div>
                        </div>
                    </div>

                    <!-- Citas del Día -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-90">Citas del Día</p>
                                <p class="text-4xl font-bold mt-2">{{ metricas.citas_hoy }}</p>
                                <p class="text-xs mt-1">
                                    ✅ {{ metricas.citas_completadas_hoy }} | ⏳ {{ metricas.citas_pendientes_hoy }}
                                </p>
                            </div>
                            <div class="text-5xl opacity-80">📅</div>
                        </div>
                    </div>

                    <!-- Ingresos del Día -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-90">Ingresos Hoy</p>
                                <p class="text-4xl font-bold mt-2">Bs. {{ formatearMoneda(metricas.ingresos_hoy) }}</p>
                                <p class="text-xs mt-1">Mes: Bs. {{ formatearMoneda(metricas.ingresos_mes) }}</p>
                            </div>
                            <div class="text-5xl opacity-80">💰</div>
                        </div>
                    </div>

                    <!-- Pacientes en Espera -->
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-90">Pacientes en Espera</p>
                                <p class="text-4xl font-bold mt-2">{{ metricas.pacientes_en_espera }}</p>
                                <p class="text-xs mt-1">
                                    Médicos: {{ metricas.medicos_activos }}/{{ metricas.total_medicos }}
                                </p>
                            </div>
                            <div class="text-5xl opacity-80">⏱️</div>
                        </div>
                    </div>
                </div>

                <!-- CONTENIDO ESPECÍFICO POR ROL -->
                <div>
                    <!-- PROPIETARIO Y ADMINISTRADOR -->
                    <DashboardAdmin v-if="rol === 'PROPIETARIO' || rol === 'ADMINISTRADOR'" :datos="datosRol" />

                    <!-- MÉDICO -->
                    <DashboardMedico v-else-if="rol === 'MEDICO'" :datos="datosRol" />

                    <!-- SECRETARIA -->
                    <DashboardSecretaria v-else-if="rol === 'SECRETARIA'" :datos="datosRol" />

                    <!-- CLIENTE -->
                    <DashboardCliente v-else-if="rol === 'CLIENTE'" :datos="datosRol" />

                    <!-- ROL NO RECONOCIDO -->
                    <div v-else class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <p class="text-yellow-800">⚠️ Rol no reconocido: {{ rol }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import DashboardAdmin from '@/Components/Dashboard/DashboardAdmin.vue';
import DashboardMedico from '@/Components/Dashboard/DashboardMedico.vue';
import DashboardSecretaria from '@/Components/Dashboard/DashboardSecretaria.vue';
import DashboardCliente from '@/Components/Dashboard/DashboardCliente.vue';

const props = defineProps({
    rol: String,
    usuario: Object,
    metricas: Object,
    datosRol: Object,
});

const fechaHoraActual = ref('');
const diaSemana = ref('');

const actualizarFechaHora = () => {
    const ahora = new Date();
    const opciones = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    };
    fechaHoraActual.value = ahora.toLocaleString('es-BO', opciones);

    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    diaSemana.value = dias[ahora.getDay()];
};

const formatearMoneda = (valor) => {
    return new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(valor || 0);
};

let intervalo;

onMounted(() => {
    actualizarFechaHora();
    intervalo = setInterval(actualizarFechaHora, 1000);
});

onUnmounted(() => {
    if (intervalo) clearInterval(intervalo);
});
</script>
