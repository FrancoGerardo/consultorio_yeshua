<template>
    <AppLayout title="Mis Pagos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                💳 Mis Pagos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">📋 Historial de Pagos</h3>

                    <!-- Tabla de Pagos -->
                    <div v-if="pagos.data && pagos.data.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servicio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="pago in pagos.data" :key="pago.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatearFecha(pago.fecha_pago) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ pago.ficha?.servicio?.nombre || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                              :class="{
                                                  'bg-green-100 text-green-800': pago.concepto === 'TOTAL',
                                                  'bg-blue-100 text-blue-800': pago.concepto === 'ANTICIPO',
                                                  'bg-purple-100 text-purple-800': pago.concepto === 'SALDO',
                                                  'bg-orange-100 text-orange-800': pago.concepto === 'CUOTA',
                                              }">
                                            {{ pago.concepto }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="flex items-center">
                                            <span v-if="pago.metodo_pago === 'EFECTIVO'">💵</span>
                                            <span v-else-if="pago.metodo_pago === 'QR'">📱</span>
                                            <span v-else-if="pago.metodo_pago === 'TARJETA'">💳</span>
                                            <span v-else-if="pago.metodo_pago === 'TRANSFERENCIA'">🏦</span>
                                            <span class="ml-2">{{ pago.metodo_pago }}</span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                        Bs. {{ parseFloat(pago.monto).toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                              :class="{
                                                  'bg-green-100 text-green-800': pago.estado === 'PAGADO',
                                                  'bg-yellow-100 text-yellow-800': pago.estado === 'PENDIENTE',
                                                  'bg-red-100 text-red-800': pago.estado === 'ANULADO',
                                              }">
                                            {{ pago.estado }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Paginación -->
                        <div class="mt-6 flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                Mostrando {{ pagos.from }} a {{ pagos.to }} de {{ pagos.total }} pagos
                            </div>
                            <div class="flex space-x-2">
                                <button v-for="link in pagos.links" :key="link.label"
                                        @click="link.url ? $inertia.visit(link.url) : null"
                                        :disabled="!link.url"
                                        v-html="link.label"
                                        :class="{
                                            'bg-blue-600 text-white': link.active,
                                            'bg-gray-200 text-gray-800': !link.active && link.url,
                                            'bg-gray-100 text-gray-400 cursor-not-allowed': !link.url,
                                        }"
                                        class="px-4 py-2 rounded-lg transition">
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje si no hay pagos -->
                    <div v-else class="text-center py-12">
                        <div class="text-6xl mb-4">💳</div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No tienes pagos registrados</h3>
                        <p class="text-gray-600 mb-6">Tus pagos aparecerán aquí una vez que realices tu primera transacción.</p>
                        <button @click="$inertia.visit(route('cliente.fichas.index'))"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Ver Mis Fichas
                        </button>
                    </div>

                    <!-- Resumen Estadístico -->
                    <div v-if="pagos.data && pagos.data.length > 0" class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Total Pagado</p>
                            <p class="text-2xl font-bold text-green-600">Bs. {{ calcularTotalPagado() }}</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Pagos Realizados</p>
                            <p class="text-2xl font-bold text-blue-600">{{ pagos.total }}</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Último Pago</p>
                            <p class="text-lg font-bold text-purple-600">
                                {{ pagos.data[0] ? formatearFecha(pagos.data[0].fecha_pago) : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botón Volver -->
                <div class="mt-6 text-center">
                    <button @click="$inertia.visit(route('dashboard'))"
                            class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        ← Volver al Dashboard
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    pagos: Object,
});

const formatearFecha = (fecha) => {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleDateString('es-BO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const calcularTotalPagado = () => {
    if (!props.pagos.data) return '0.00';
    const total = props.pagos.data
        .filter(pago => pago.estado === 'PAGADO')
        .reduce((sum, pago) => sum + parseFloat(pago.monto), 0);
    return total.toFixed(2);
};
</script>

