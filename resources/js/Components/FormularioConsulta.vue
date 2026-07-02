<template>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold mb-4">📝 Nueva Consulta</h3>

        <form @submit.prevent="$emit('guardar', formulario)">
            <!-- Tipo de Consulta -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo de Consulta <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="formulario.tipo_consulta"
                    required
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                >
                    <option value="PRIMERA_VEZ">Primera Vez</option>
                    <option value="CONTROL">Control</option>
                    <option value="EMERGENCIA">Emergencia</option>
                </select>
            </div>

            <!-- Motivo de Consulta -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Motivo de Consulta <span class="text-red-500">*</span>
                </label>
                <textarea
                    v-model="formulario.motivo_consulta"
                    required
                    rows="3"
                    placeholder="Describa el motivo de la consulta..."
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                ></textarea>
            </div>

            <!-- Signos Vitales -->
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <h4 class="font-bold mb-3">❤️ Signos Vitales</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Presión Arterial</label>
                        <input
                            v-model="formulario.presion_arterial"
                            type="text"
                            placeholder="120/80"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Frecuencia Cardíaca (bpm)</label>
                        <input
                            v-model="formulario.frecuencia_cardiaca"
                            type="number"
                            placeholder="75"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Temperatura (°C)</label>
                        <input
                            v-model="formulario.temperatura"
                            type="number"
                            step="0.1"
                            placeholder="36.5"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Peso (kg)</label>
                        <input
                            v-model="formulario.peso"
                            type="number"
                            step="0.1"
                            placeholder="70.5"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Saturación O2 (%)</label>
                        <input
                            v-model="formulario.saturacion_oxigeno"
                            type="number"
                            placeholder="98"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Frecuencia Resp. (rpm)</label>
                        <input
                            v-model="formulario.frecuencia_respiratoria"
                            type="number"
                            placeholder="16"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm"
                        />
                    </div>
                </div>
            </div>

            <!-- Diagnóstico -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Diagnóstico <span class="text-red-500">*</span>
                </label>
                <textarea
                    v-model="formulario.diagnostico"
                    required
                    rows="3"
                    placeholder="Diagnóstico médico..."
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                ></textarea>
            </div>

            <!-- Código CIE-10 -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Código CIE-10
                </label>
                <input
                    v-model="formulario.codigo_cie10"
                    type="text"
                    placeholder="J06.9"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                />
            </div>

            <!-- Tratamiento -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tratamiento <span class="text-red-500">*</span>
                </label>
                <textarea
                    v-model="formulario.tratamiento"
                    required
                    rows="4"
                    placeholder="Indicaciones de tratamiento..."
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                ></textarea>
            </div>

            <!-- Exámenes Solicitados -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Exámenes Solicitados
                </label>
                <textarea
                    v-model="formulario.examenes_solicitados"
                    rows="2"
                    placeholder="Hemograma completo, radiografía..."
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                ></textarea>
            </div>

            <!-- Observaciones -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Observaciones
                </label>
                <textarea
                    v-model="formulario.observaciones"
                    rows="2"
                    placeholder="Observaciones adicionales..."
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                ></textarea>
            </div>

            <!-- Botones -->
            <div class="flex gap-4">
                <button
                    type="submit"
                    class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold"
                >
                    💾 Guardar Consulta
                </button>
                <button
                    type="button"
                    @click="$emit('cancelar')"
                    class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-semibold"
                >
                    ✖️ Cancelar
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive } from 'vue';

defineEmits(['guardar', 'cancelar']);

const formulario = reactive({
    tipo_consulta: 'CONTROL',
    motivo_consulta: '',
    presion_arterial: '',
    frecuencia_cardiaca: '',
    temperatura: '',
    peso: '',
    saturacion_oxigeno: '',
    frecuencia_respiratoria: '',
    diagnostico: '',
    codigo_cie10: '',
    tratamiento: '',
    examenes_solicitados: '',
    observaciones: '',
});
</script>

