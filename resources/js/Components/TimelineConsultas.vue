<template>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold mb-4">⏱️ Timeline de Consultas</h3>

        <!-- Si no hay seguimientos -->
        <div v-if="!seguimientos || seguimientos.length === 0" class="text-center py-8 text-gray-500">
            <p>📭 No hay consultas registradas aún</p>
        </div>

        <!-- Lista de seguimientos/consultas -->
        <div v-else class="space-y-6">
            <div
                v-for="(seguimiento, index) in seguimientos"
                :key="seguimiento.id"
                class="border-l-4 border-blue-500 pl-4 pb-6 relative"
                :class="{ 'border-gray-300': index === seguimientos.length - 1 }"
            >
                <!-- Punto en la línea -->
                <div class="absolute -left-2 top-0 w-4 h-4 bg-blue-500 rounded-full"></div>

                <!-- Fecha y Médico -->
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="text-sm text-gray-600">{{ formatFecha(seguimiento.fecha_consulta) }}</p>
                        <p class="font-bold text-gray-900">
                            Dr(a). {{ seguimiento.medico?.usuario?.persona?.nombre_completo || 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500" v-if="seguimiento.medico?.especialidad">
                            {{ seguimiento.medico.especialidad.nombre }}
                        </p>
                    </div>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                        {{ seguimiento.tipo_consulta }}
                    </span>
                </div>

                <!-- Motivo -->
                <div class="mb-2">
                    <p class="text-sm font-semibold text-gray-700">Motivo:</p>
                    <p class="text-sm">{{ seguimiento.motivo_consulta }}</p>
                </div>

                <!-- Diagnóstico -->
                <div class="mb-2" v-if="seguimiento.diagnostico">
                    <p class="text-sm font-semibold text-gray-700">Diagnóstico:</p>
                    <p class="text-sm">{{ seguimiento.diagnostico }}</p>
                    <p v-if="seguimiento.codigo_cie10" class="text-xs text-gray-500 mt-1">
                        CIE-10: {{ seguimiento.codigo_cie10 }}
                    </p>
                </div>

                <!-- Tratamiento -->
                <div class="mb-2" v-if="seguimiento.tratamiento">
                    <p class="text-sm font-semibold text-gray-700">Tratamiento:</p>
                    <p class="text-sm">{{ seguimiento.tratamiento }}</p>
                </div>

                <!-- Signos Vitales -->
                <div
                    v-if="seguimiento.presion_arterial || seguimiento.frecuencia_cardiaca || seguimiento.temperatura"
                    class="bg-gray-50 p-3 rounded mt-2"
                >
                    <p class="text-sm font-semibold text-gray-700 mb-1">Signos Vitales:</p>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <div v-if="seguimiento.presion_arterial">
                            <span class="text-gray-600">PA:</span>
                            <span class="font-bold">{{ seguimiento.presion_arterial }} mmHg</span>
                        </div>
                        <div v-if="seguimiento.frecuencia_cardiaca">
                            <span class="text-gray-600">FC:</span>
                            <span class="font-bold">{{ seguimiento.frecuencia_cardiaca }} bpm</span>
                        </div>
                        <div v-if="seguimiento.temperatura">
                            <span class="text-gray-600">Temp:</span>
                            <span class="font-bold">{{ seguimiento.temperatura }}°C</span>
                        </div>
                        <div v-if="seguimiento.peso">
                            <span class="text-gray-600">Peso:</span>
                            <span class="font-bold">{{ seguimiento.peso }} kg</span>
                        </div>
                        <div v-if="seguimiento.saturacion_oxigeno">
                            <span class="text-gray-600">Sat O2:</span>
                            <span class="font-bold">{{ seguimiento.saturacion_oxigeno }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div v-if="seguimiento.observaciones" class="mt-2 text-xs text-gray-600 italic">
                    {{ seguimiento.observaciones }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    seguimientos: Array,
});

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
</script>

