<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetalleFichaClienteModal from '@/Components/DetalleFichaClienteModal.vue';

defineProps({
    fichas: Object,
});

const page = usePage();
const modalDetalle = ref(false);
const fichaDetalle = ref(null);

function getEstadoClass(ficha) {
    if (estaEsperandoConfirmacionPago(ficha)) {
        return 'bg-blue-100 text-blue-800';
    }

    const clases = {
        'PENDIENTE_PAGO': 'bg-amber-100 text-amber-800',
        'ANTICIPO_PAGADO': 'bg-sky-100 text-sky-800',
        'PAGADA_COMPLETA': 'bg-emerald-100 text-emerald-800',
        'PENDIENTE': 'bg-yellow-100 text-yellow-800',
        'CONFIRMADA': 'bg-blue-100 text-blue-800',
        'ATENDIDA': 'bg-green-100 text-green-800',
        'CANCELADA': 'bg-red-100 text-red-800',
    };
    return clases[ficha.estado] || 'bg-gray-100 text-gray-800';
}

const etiquetasEstado = {
    'PENDIENTE_PAGO': 'Pendiente de pago',
    'ANTICIPO_PAGADO': 'Anticipo pagado',
    'PAGADA_COMPLETA': 'Pagada completa',
    'PENDIENTE': 'Pendiente',
    'CONFIRMADA': 'Confirmada',
    'ATENDIDA': 'Atendida',
    'CANCELADA': 'Cancelada',
};

function getEstadoTexto(ficha) {
    if (estaEsperandoConfirmacionPago(ficha)) {
        return 'Esperando confirmación de pago';
    }

    return etiquetasEstado[ficha.estado] || (ficha.estado || '').replace(/_/g, ' ');
}

function estaEsperandoConfirmacionPago(ficha) {
    return Boolean(ficha.tiene_pago_pendiente);
}

function formatearFecha(fecha) {
    if (!fecha) return '';
    return new Date(fecha).toLocaleDateString('es-BO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function formatearHora(hora) {
    if (!hora) return '';
    if (typeof hora === 'string') {
        const valorHora = hora.includes('T') ? hora.split('T')[1] : hora;
        const partes = valorHora.split(':');
        return `${partes[0]}:${partes[1]}`;
    }
    return hora;
}

function formatearMoneda(monto) {
    if (monto == null) return '';
    return `${Number(monto).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })} Bs.`;
}

function puedeGestionarPago(ficha) {
    return ficha.estado === 'PENDIENTE_PAGO' && !estaEsperandoConfirmacionPago(ficha);
}

function puedePagarSaldo(ficha) {
    return ficha.estado === 'ANTICIPO_PAGADO'
        && Number(ficha.saldo_pendiente) > 0
        && !estaEsperandoConfirmacionPago(ficha);
}

function mostrarResumenPago(ficha) {
    if (ficha.estado === 'CANCELADA') {
        return false;
    }

    return Number(ficha.total_pagado) > 0 || Number(ficha.saldo_pendiente) > 0;
}

function abrirDetalle(ficha) {
    fichaDetalle.value = ficha;
    modalDetalle.value = true;
}

function cerrarDetalle() {
    modalDetalle.value = false;
    fichaDetalle.value = null;
}

function cancelarFicha(fichaId) {
    if (!confirm('¿Estás seguro de cancelar esta ficha? El horario quedará disponible para otros pacientes.')) {
        return;
    }

    cerrarDetalle();

    router.post(route('cliente.fichas.cancelar', fichaId), {}, {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout title="Mis Fichas">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mis Fichas
                </h2>
                <Link
                    :href="route('cliente.fichas.create')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition"
                >
                    + Generar Nueva Ficha
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    v-if="page.props.flash?.success"
                    class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800"
                >
                    {{ page.props.flash.success }}
                </div>
                <div
                    v-if="page.props.flash?.error"
                    class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800"
                >
                    {{ page.props.flash.error }}
                </div>

                <div v-if="fichas.data && fichas.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="ficha in fichas.data"
                        :key="ficha.id"
                        class="bg-white rounded-lg shadow-md transition overflow-hidden flex flex-col"
                    >
                        <div
                            class="bg-gradient-to-r from-indigo-600 to-purple-600 p-4 cursor-pointer select-none transition hover:brightness-110"
                            role="button"
                            tabindex="0"
                            title="Ver detalle de la ficha"
                            @click="abrirDetalle(ficha)"
                            @keydown.enter="abrirDetalle(ficha)"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-lg font-bold text-white">{{ ficha.servicio?.nombre || 'Sin servicio' }}</h3>
                                <span class="text-xs text-indigo-200 shrink-0">Ver detalle</span>
                            </div>
                            <span :class="['inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold', getEstadoClass(ficha)]">
                                {{ getEstadoTexto(ficha) }}
                            </span>
                        </div>

                        <div class="p-6 flex-1">
                            <div class="space-y-3">
                                <div>
                                    <span class="text-gray-500 text-sm">Médico:</span>
                                    <p class="font-semibold text-gray-800">
                                        {{ ficha.medico?.usuario?.persona?.nombre_completo || 'Sin médico' }}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-gray-500 text-sm">Fecha:</span>
                                    <p class="font-semibold text-gray-800">{{ formatearFecha(ficha.fecha) }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500 text-sm">Hora:</span>
                                    <p class="font-semibold text-gray-800">{{ formatearHora(ficha.hora) }}</p>
                                </div>
                                <div v-if="ficha.motivo_consulta">
                                    <span class="text-gray-500 text-sm">Motivo:</span>
                                    <p class="text-gray-800 text-sm mt-1">{{ ficha.motivo_consulta }}</p>
                                </div>
                                <div
                                    v-if="mostrarResumenPago(ficha)"
                                    class="rounded-lg bg-gray-50 border border-gray-200 p-3 space-y-1"
                                >
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Total acordado:</span>
                                        <span class="font-semibold text-gray-800">{{ formatearMoneda(ficha.costo_total) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Pagado:</span>
                                        <span class="font-semibold text-emerald-700">{{ formatearMoneda(ficha.total_pagado) }}</span>
                                    </div>
                                    <div
                                        v-if="Number(ficha.saldo_pendiente) > 0"
                                        class="flex justify-between text-sm"
                                    >
                                        <span class="text-gray-500">Saldo pendiente:</span>
                                        <span class="font-semibold text-amber-700">{{ formatearMoneda(ficha.saldo_pendiente) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="puedePagarSaldo(ficha)"
                            class="px-6 pb-6 pt-0"
                        >
                            <Link
                                :href="route('cliente.pagos.procesar', ficha.id)"
                                class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition"
                            >
                                Pagar saldo
                            </Link>
                        </div>

                        <div
                            v-if="puedeGestionarPago(ficha)"
                            class="px-6 pb-6 pt-0 flex gap-3"
                        >
                            <Link
                                :href="route('cliente.pagos.seleccionar-plan', ficha.id)"
                                class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition"
                            >
                                Pagar
                            </Link>
                            <button
                                type="button"
                                @click="cancelarFicha(ficha.id)"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12">
                    <p class="text-gray-500 text-lg mb-4">No tienes fichas registradas.</p>
                    <Link
                        :href="route('cliente.fichas.create')"
                        class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition"
                    >
                        Generar Primera Ficha
                    </Link>
                </div>

                <div v-if="fichas.links && fichas.links.length > 3" class="mt-6 flex justify-center">
                    <div class="flex space-x-2">
                        <Link
                            v-for="link in fichas.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="['px-4 py-2 rounded-lg', link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100']"
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>
        </div>

        <DetalleFichaClienteModal
            :show="modalDetalle"
            :ficha="fichaDetalle"
            @close="cerrarDetalle"
            @cancelar-ficha="cancelarFicha"
        />
    </AppLayout>
</template>
