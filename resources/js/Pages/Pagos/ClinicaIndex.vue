<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    pagos: Object,
    filtros: Object,
});

const form = ref({
    buscar: props.filtros?.buscar ?? '',
    estado: props.filtros?.estado ?? '',
    metodo_pago: props.filtros?.metodo_pago ?? '',
    desde: props.filtros?.desde ?? '',
    hasta: props.filtros?.hasta ?? '',
});

const formatearFecha = (fecha) => {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleString('es-BO', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const aplicarFiltros = () => {
    router.get(route('pagos.index'), {
        buscar: form.value.buscar || undefined,
        estado: form.value.estado || undefined,
        metodo_pago: form.value.metodo_pago || undefined,
        desde: form.value.desde || undefined,
        hasta: form.value.hasta || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Auto-aplicar filtros con un pequeño debounce para búsqueda
let t = null;
watch(() => form.value.buscar, () => {
    if (t) clearTimeout(t);
    t = setTimeout(() => aplicarFiltros(), 300);
});

const limpiar = () => {
    form.value = { buscar: '', estado: '', metodo_pago: '', desde: '', hasta: '' };
    aplicarFiltros();
};

const totalPagina = computed(() => {
    if (!props.pagos?.data) return '0.00';
    const total = props.pagos.data.reduce((s, p) => s + parseFloat(p.monto || 0), 0);
    return total.toFixed(2);
});
</script>

<template>
    <AppLayout title="Pagos (Consultorio)">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    💳 Pagos (Consultorio)
                </h2>
                <div class="text-sm text-gray-600">
                    Total en página: <span class="font-semibold text-green-600">Bs. {{ totalPagina }}</span>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="bg-white shadow-md rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">🔎 Filtros</h3>

                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="md:col-span-2">
                            <label class="text-sm text-gray-600">Buscar (CI / Nombre / Servicio)</label>
                            <input v-model="form.buscar" type="text" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="Ej: 11111111, Juan, Control de Salud" />
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Estado</label>
                            <select v-model="form.estado" @change="aplicarFiltros" class="mt-1 w-full border rounded-lg px-3 py-2">
                                <option value="">Todos</option>
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="PAGADO">PAGADO</option>
                                <option value="ANULADO">ANULADO</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Método</label>
                            <select v-model="form.metodo_pago" @change="aplicarFiltros" class="mt-1 w-full border rounded-lg px-3 py-2">
                                <option value="">Todos</option>
                                <option value="EFECTIVO">EFECTIVO</option>
                                <option value="QR">QR</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button @click="limpiar" class="w-full px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200">
                                Limpiar
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-gray-600">Desde</label>
                            <input v-model="form.desde" @change="aplicarFiltros" type="date" class="mt-1 w-full border rounded-lg px-3 py-2" />
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Hasta</label>
                            <input v-model="form.hasta" @change="aplicarFiltros" type="date" class="mt-1 w-full border rounded-lg px-3 py-2" />
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-md rounded-lg p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">📋 Pagos registrados</h3>

                    <div v-if="pagos.data && pagos.data.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servicio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Médico</th>
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
                                        <div class="font-semibold">
                                            {{ pago.ficha?.cliente?.usuario?.persona?.nombre_completo || 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            CI: {{ pago.ficha?.cliente?.usuario?.persona?.dni || 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ pago.ficha?.servicio?.nombre || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ pago.ficha?.medico?.usuario?.persona?.nombre_completo || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ pago.concepto || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ pago.metodo_pago || 'N/A' }}
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

                    <div v-else class="text-center py-12">
                        <div class="text-6xl mb-4">💳</div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay pagos para mostrar</h3>
                        <p class="text-gray-600">Ajusta los filtros o espera nuevos registros.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


