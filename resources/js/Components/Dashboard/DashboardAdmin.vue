<template>
    <div class="space-y-6">
        
        <!-- Accesos Rápidos -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">⚡ Accesos Rápidos</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <button
                    @click="$inertia.visit(route('usuarios.index'))"
                    class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">👥</span>
                    <span class="text-sm font-semibold text-gray-700">Usuarios</span>
                </button>
                <button
                    @click="$inertia.visit(route('reportes.generar'))"
                    class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">📊</span>
                    <span class="text-sm font-semibold text-gray-700">Reportes</span>
                </button>
                <button
                    @click="$inertia.visit(route('pagos.index'))"
                    class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">💳</span>
                    <span class="text-sm font-semibold text-gray-700">Pagos</span>
                </button>
                <button
                    @click="$inertia.visit(route('historiales-clinicos.index'))"
                    class="flex flex-col items-center p-4 bg-red-50 hover:bg-red-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">📋</span>
                    <span class="text-sm font-semibold text-gray-700">Historiales</span>
                </button>
                <button
                    @click="$inertia.visit(route('servicios.index'))"
                    class="flex flex-col items-center p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">🏥</span>
                    <span class="text-sm font-semibold text-gray-700">Servicios</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Gráfico de Citas de la Semana -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold mb-4">📈 Citas de la Semana</h3>
                <div class="h-64">
                    <canvas ref="graficoCitas"></canvas>
                </div>
            </div>

            <!-- Servicios Más Solicitados -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold mb-4">🔥 Servicios Populares (Este Mes)</h3>
                <div class="space-y-3">
                    <div
                        v-for="(servicio, index) in datos.servicios_populares"
                        :key="index"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ index + 1 }}.</span>
                            <span class="font-semibold">{{ servicio.nombre }}</span>
                        </div>
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-bold">
                            {{ servicio.total }}
                        </span>
                    </div>
                    <p v-if="!datos.servicios_populares || datos.servicios_populares.length === 0" class="text-center text-gray-500">
                        No hay datos disponibles
                    </p>
                </div>
            </div>
        </div>

        <!-- Gráfico de Ingresos -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">💰 Ingresos de la Semana</h3>
            <div class="h-64">
                <canvas ref="graficoIngresos"></canvas>
            </div>
        </div>

        <!-- Resumen Financiero -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">💼 Resumen Financiero</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg p-4">
                    <p class="text-sm text-gray-600">Pagos Pendientes</p>
                    <p class="text-3xl font-bold text-orange-600">{{ datos.resumen_financiero?.pagos_pendientes || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">
                        Monto: Bs. {{ formatearMoneda(datos.resumen_financiero?.monto_pendiente || 0) }}
                    </p>
                </div>
                <div class="bg-white rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total Pacientes</p>
                    <p class="text-3xl font-bold text-blue-600">{{ datos.resumen_financiero?.total_pacientes || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Registrados en el sistema</p>
                </div>
                <div class="bg-white rounded-lg p-4">
                    <p class="text-sm text-gray-600">Citas Hoy</p>
                    <p class="text-3xl font-bold text-green-600">{{ datos.citas_hoy?.length || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Programadas para hoy</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    datos: Object,
});

const graficoCitas = ref(null);
const graficoIngresos = ref(null);

const formatearMoneda = (valor) => {
    return new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(valor || 0);
};

onMounted(() => {
    // Gráfico de Citas
    if (graficoCitas.value && props.datos.grafico_citas_semana) {
        new Chart(graficoCitas.value, {
            type: 'line',
            data: {
                labels: props.datos.grafico_citas_semana.labels,
                datasets: [{
                    label: 'Citas',
                    data: props.datos.grafico_citas_semana.datos,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                        },
                    },
                },
            },
        });
    }

    // Gráfico de Ingresos
    if (graficoIngresos.value && props.datos.grafico_ingresos) {
        new Chart(graficoIngresos.value, {
            type: 'bar',
            data: {
                labels: props.datos.grafico_ingresos.labels,
                datasets: [{
                    label: 'Ingresos (Bs.)',
                    data: props.datos.grafico_ingresos.datos,
                    backgroundColor: 'rgba(147, 51, 234, 0.6)',
                    borderColor: 'rgb(147, 51, 234)',
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });
    }
});
</script>

