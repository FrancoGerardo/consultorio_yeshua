<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    ficha: {
        type: Object,
        default: null,
    },
    pagoDestacado: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'cancelar-ficha']);

const etiquetasEstado = {
    PENDIENTE_PAGO: 'Pendiente de pago',
    ANTICIPO_PAGADO: 'Anticipo pagado',
    PAGADA_COMPLETA: 'Pagada completa',
    PENDIENTE: 'Pendiente',
    CONFIRMADA: 'Confirmada',
    ATENDIDA: 'Atendida',
    CANCELADA: 'Cancelada',
};

const etiquetasConcepto = {
    TOTAL: 'Pago total',
    ANTICIPO: 'Anticipo',
    SALDO: 'Saldo',
    CUOTA: 'Cuota',
    ABONO: 'Abono',
};

const etiquetasEstadoPago = {
    PAGADO: 'Pagado',
    PENDIENTE: 'Pendiente',
    ANULADO: 'Anulado',
};

const pagosDetalle = computed(() => {
    if (!props.ficha?.pagos?.length) {
        return [];
    }

    return [...props.ficha.pagos].sort((a, b) => {
        const fechaA = new Date(a.fecha_pago || a.created_at || 0).getTime();
        const fechaB = new Date(b.fecha_pago || b.created_at || 0).getTime();
        return fechaB - fechaA;
    });
});

function estaEsperandoConfirmacionPago(ficha) {
    return Boolean(ficha?.tiene_pago_pendiente);
}

function getEstadoClass(ficha) {
    if (estaEsperandoConfirmacionPago(ficha)) {
        return 'bg-blue-100 text-blue-800';
    }

    const clases = {
        PENDIENTE_PAGO: 'bg-amber-100 text-amber-800',
        ANTICIPO_PAGADO: 'bg-sky-100 text-sky-800',
        PAGADA_COMPLETA: 'bg-emerald-100 text-emerald-800',
        PENDIENTE: 'bg-yellow-100 text-yellow-800',
        CONFIRMADA: 'bg-blue-100 text-blue-800',
        ATENDIDA: 'bg-green-100 text-green-800',
        CANCELADA: 'bg-red-100 text-red-800',
    };

    return clases[ficha?.estado] || 'bg-gray-100 text-gray-800';
}

function getEstadoTexto(ficha) {
    if (estaEsperandoConfirmacionPago(ficha)) {
        return 'Esperando confirmación de pago';
    }

    return etiquetasEstado[ficha?.estado] || (ficha?.estado || '').replace(/_/g, ' ');
}

function getEstadoPagoClass(estado) {
    return {
        PAGADO: 'bg-green-100 text-green-800',
        PENDIENTE: 'bg-yellow-100 text-yellow-800',
        ANULADO: 'bg-red-100 text-red-800',
    }[estado] || 'bg-gray-100 text-gray-800';
}

function getConceptoClass(concepto) {
    return {
        TOTAL: 'bg-green-100 text-green-800',
        ANTICIPO: 'bg-blue-100 text-blue-800',
        SALDO: 'bg-purple-100 text-purple-800',
        CUOTA: 'bg-orange-100 text-orange-800',
    }[concepto] || 'bg-gray-100 text-gray-800';
}

function formatearFecha(fecha) {
    if (!fecha) return '';
    return new Date(fecha).toLocaleDateString('es-BO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function formatearFechaHora(fecha) {
    if (!fecha) return '—';
    return new Date(fecha).toLocaleString('es-BO', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
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

function codigoFicha(id) {
    if (!id) return '—';
    return id.slice(-8).toUpperCase();
}

function puedeGestionarPago(ficha) {
    return ficha?.estado === 'PENDIENTE_PAGO' && !estaEsperandoConfirmacionPago(ficha);
}

function puedePagarSaldo(ficha) {
    return ficha?.estado === 'ANTICIPO_PAGADO'
        && Number(ficha.saldo_pendiente) > 0
        && !estaEsperandoConfirmacionPago(ficha);
}

function cerrar() {
    emit('close');
}
</script>

<template>
    <DialogModal :show="show" max-width="2xl" @close="cerrar">
        <template #title>
            {{ pagoDestacado ? 'Detalle del pago' : 'Detalle de la ficha' }}
        </template>

        <template #content>
            <div v-if="ficha" class="space-y-6 text-sm text-gray-700">
                <section
                    v-if="pagoDestacado"
                    class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 space-y-3"
                >
                    <h4 class="font-semibold text-indigo-900">Este pago</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <p class="text-indigo-700/80">Fecha</p>
                            <p class="font-medium text-indigo-950">
                                {{ formatearFechaHora(pagoDestacado.fecha_pago || pagoDestacado.created_at) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-indigo-700/80">Monto</p>
                            <p class="font-semibold text-indigo-950">{{ formatearMoneda(pagoDestacado.monto) }}</p>
                        </div>
                        <div>
                            <p class="text-indigo-700/80">Concepto</p>
                            <span
                                :class="['inline-block px-3 py-1 rounded-full text-xs font-semibold', getConceptoClass(pagoDestacado.concepto)]"
                            >
                                {{ etiquetasConcepto[pagoDestacado.concepto] || pagoDestacado.concepto }}
                            </span>
                        </div>
                        <div>
                            <p class="text-indigo-700/80">Método</p>
                            <p class="font-medium text-indigo-950">{{ pagoDestacado.metodo_pago || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-indigo-700/80">Estado</p>
                            <span
                                :class="['inline-block px-3 py-1 rounded-full text-xs font-semibold', getEstadoPagoClass(pagoDestacado.estado)]"
                            >
                                {{ etiquetasEstadoPago[pagoDestacado.estado] || pagoDestacado.estado }}
                            </span>
                        </div>
                        <div v-if="pagoDestacado.company_transaction_id">
                            <p class="text-indigo-700/80">Referencia</p>
                            <p class="font-medium text-indigo-950 text-xs break-all">
                                {{ pagoDestacado.company_transaction_id }}
                            </p>
                        </div>
                    </div>
                </section>

                <div class="flex flex-wrap items-center gap-2">
                    <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getEstadoClass(ficha)]">
                        {{ getEstadoTexto(ficha) }}
                    </span>
                    <span class="text-xs text-gray-500">Código: {{ codigoFicha(ficha.id) }}</span>
                </div>

                <section class="rounded-lg border border-gray-200 p-4 space-y-3">
                    <h4 class="font-semibold text-gray-900">Información de la cita</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <p class="text-gray-500">Servicio</p>
                            <p class="font-medium">{{ ficha.servicio?.nombre || 'Sin servicio' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Médico</p>
                            <p class="font-medium">{{ ficha.medico?.usuario?.persona?.nombre_completo || 'Sin médico' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Fecha</p>
                            <p class="font-medium">{{ formatearFecha(ficha.fecha) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Hora</p>
                            <p class="font-medium">{{ formatearHora(ficha.hora) || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Sala</p>
                            <p class="font-medium">
                                {{ ficha.sala?.numero ? `Sala ${ficha.sala.numero}` : 'Por asignar' }}
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-gray-500">Motivo de consulta</p>
                            <p class="font-medium">{{ ficha.motivo_consulta || 'Sin motivo registrado' }}</p>
                        </div>
                    </div>
                </section>

                <section
                    v-if="ficha.estado !== 'CANCELADA'"
                    class="rounded-lg border border-gray-200 p-4 space-y-3"
                >
                    <h4 class="font-semibold text-gray-900">Resumen de pagos de la ficha</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-gray-500 text-xs">Total acordado</p>
                            <p class="font-semibold text-gray-900">{{ formatearMoneda(ficha.costo_total) }}</p>
                        </div>
                        <div class="rounded-lg bg-emerald-50 p-3">
                            <p class="text-emerald-700 text-xs">Pagado</p>
                            <p class="font-semibold text-emerald-800">{{ formatearMoneda(ficha.total_pagado) }}</p>
                        </div>
                        <div class="rounded-lg bg-amber-50 p-3">
                            <p class="text-amber-700 text-xs">Saldo pendiente</p>
                            <p class="font-semibold text-amber-800">{{ formatearMoneda(ficha.saldo_pendiente) }}</p>
                        </div>
                    </div>

                    <div v-if="pagosDetalle.length > 0" class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="border-b border-gray-200 text-left text-gray-500">
                                    <th class="py-2 pr-3">Fecha</th>
                                    <th class="py-2 pr-3">Concepto</th>
                                    <th class="py-2 pr-3">Método</th>
                                    <th class="py-2 pr-3">Estado</th>
                                    <th class="py-2 text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="pago in pagosDetalle"
                                    :key="pago.id"
                                    :class="[
                                        'border-b border-gray-100',
                                        pagoDestacado?.id === pago.id ? 'bg-indigo-50' : '',
                                    ]"
                                >
                                    <td class="py-2 pr-3">{{ formatearFechaHora(pago.fecha_pago || pago.created_at) }}</td>
                                    <td class="py-2 pr-3">{{ etiquetasConcepto[pago.concepto] || pago.concepto }}</td>
                                    <td class="py-2 pr-3">{{ pago.metodo_pago || '—' }}</td>
                                    <td class="py-2 pr-3">{{ etiquetasEstadoPago[pago.estado] || pago.estado }}</td>
                                    <td class="py-2 text-right font-medium">{{ formatearMoneda(pago.monto) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-gray-500">Sin pagos registrados.</p>
                </section>
            </div>
        </template>

        <template #footer>
            <div class="flex flex-wrap gap-3 justify-end">
                <Link
                    v-if="ficha && puedePagarSaldo(ficha)"
                    :href="route('cliente.pagos.procesar', ficha.id)"
                    class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition"
                >
                    Pagar saldo
                </Link>
                <Link
                    v-if="ficha && puedeGestionarPago(ficha)"
                    :href="route('cliente.pagos.seleccionar-plan', ficha.id)"
                    class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition"
                >
                    Pagar
                </Link>
                <button
                    v-if="ficha && puedeGestionarPago(ficha)"
                    type="button"
                    class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition"
                    @click="emit('cancelar-ficha', ficha.id)"
                >
                    Cancelar ficha
                </button>
                <PrimaryButton @click="cerrar">
                    Cerrar
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>
