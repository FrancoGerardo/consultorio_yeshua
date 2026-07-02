<template>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold mb-4">📖 Consultas Previas</h3>

        <!-- Si no hay seguimientos -->
        <div v-if="!seguimientos || seguimientos.length === 0" class="text-center py-8 text-gray-500">
            <p>📭 No hay consultas previas registradas</p>
        </div>

        <!-- Lista de seguimientos -->
        <div v-else class="space-y-4">
            <div
                v-for="seguimiento in seguimientos"
                :key="seguimiento.id"
                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
            >
                <!-- Encabezado -->
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-sm text-gray-600">{{ formatFecha(seguimiento.fecha_consulta) }}</p>
                        <p class="font-bold text-gray-900">
                            Dr(a). {{ seguimiento.medico?.usuario?.persona?.nombre_completo || 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500" v-if="seguimiento.medico?.especialidad">
                            {{ seguimiento.medico.especialidad.nombre }}
                        </p>
                    </div>
                    <span
                        class="px-3 py-1 rounded-full text-xs font-semibold"
                        :class="{
                            'bg-blue-100 text-blue-800': seguimiento.tipo_consulta === 'PRIMERA_VEZ',
                            'bg-green-100 text-green-800': seguimiento.tipo_consulta === 'CONTROL',
                            'bg-red-100 text-red-800': seguimiento.tipo_consulta === 'EMERGENCIA',
                        }"
                    >
                        {{ formatTipoConsulta(seguimiento.tipo_consulta) }}
                    </span>
                </div>

                <!-- Motivo -->
                <div class="mb-2">
                    <p class="text-xs font-semibold text-gray-600">Motivo de Consulta:</p>
                    <p class="text-sm">{{ seguimiento.motivo_consulta }}</p>
                </div>

                <!-- Diagnóstico -->
                <div class="mb-2" v-if="seguimiento.diagnostico">
                    <p class="text-xs font-semibold text-gray-600">Diagnóstico:</p>
                    <p class="text-sm">{{ seguimiento.diagnostico }}</p>
                    <p v-if="seguimiento.codigo_cie10" class="text-xs text-gray-500 mt-1">
                        <strong>CIE-10:</strong> {{ seguimiento.codigo_cie10 }}
                    </p>
                </div>

                <!-- Tratamiento -->
                <div class="mb-2" v-if="seguimiento.tratamiento">
                    <p class="text-xs font-semibold text-gray-600">Tratamiento:</p>
                    <p class="text-sm">{{ seguimiento.tratamiento }}</p>
                </div>

                <!-- Signos Vitales (resumen) -->
                <div
                    v-if="
                        seguimiento.presion_arterial ||
                        seguimiento.frecuencia_cardiaca ||
                        seguimiento.temperatura ||
                        seguimiento.peso
                    "
                    class="bg-gray-50 p-2 rounded mt-2"
                >
                    <p class="text-xs font-semibold text-gray-600 mb-1">Signos Vitales:</p>
                    <div class="flex flex-wrap gap-3 text-xs">
                        <div v-if="seguimiento.presion_arterial">
                            <span class="text-gray-600">PA:</span>
                            <span class="font-bold ml-1">{{ seguimiento.presion_arterial }}</span>
                        </div>
                        <div v-if="seguimiento.frecuencia_cardiaca">
                            <span class="text-gray-600">FC:</span>
                            <span class="font-bold ml-1">{{ seguimiento.frecuencia_cardiaca }} bpm</span>
                        </div>
                        <div v-if="seguimiento.temperatura">
                            <span class="text-gray-600">Temp:</span>
                            <span class="font-bold ml-1">{{ seguimiento.temperatura }}°C</span>
                        </div>
                        <div v-if="seguimiento.peso">
                            <span class="text-gray-600">Peso:</span>
                            <span class="font-bold ml-1">{{ seguimiento.peso }} kg</span>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div v-if="seguimiento.observaciones" class="mt-2 bg-yellow-50 p-2 rounded">
                    <p class="text-xs font-semibold text-gray-600">Observaciones:</p>
                    <p class="text-xs italic">{{ seguimiento.observaciones }}</p>
                </div>

                <!-- Botón expandir (opcional, para futuras mejoras) -->
                <div class="mt-3 text-right">
                    <button
                        @click="$emit('verDetalle', seguimiento.id)"
                        class="text-xs text-blue-600 hover:text-blue-800 font-semibold"
                    >
                        Ver Detalle Completo →
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    seguimientos: Array,
});

defineEmits(['verDetalle']);

const formatFecha = (fecha) => {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleString('es-BO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatTipoConsulta = (tipo) => {
    const tipos = {
        PRIMERA_VEZ: 'Primera Vez',
        CONTROL: 'Control',
        EMERGENCIA: 'Emergencia',
    };
    return tipos[tipo] || tipo;
};
</script>

