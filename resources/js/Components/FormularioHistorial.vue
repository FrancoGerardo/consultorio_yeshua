<template>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold mb-4">✏️ Actualizar Historial Permanente</h3>

        <form @submit.prevent="$emit('guardar', formulario)">
            
            <!-- Información Crítica -->
            <div class="bg-red-50 p-4 rounded-lg mb-4">
                <h4 class="font-bold text-red-900 mb-3">🩸 Información Crítica</h4>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Grupo Sanguíneo <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="formulario.grupo_sanguineo"
                            required
                            class="w-full border-gray-300 rounded-md shadow-sm"
                        >
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="AB">AB</option>
                            <option value="O">O</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Factor RH <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="formulario.factor_rh"
                            required
                            class="w-full border-gray-300 rounded-md shadow-sm"
                        >
                            <option value="Positivo">Positivo (+)</option>
                            <option value="Negativo">Negativo (-)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        🚨 Alergias
                    </label>
                    <textarea
                        v-model="formulario.alergias"
                        rows="2"
                        placeholder="Alergias conocidas..."
                        class="w-full border-gray-300 rounded-md shadow-sm"
                    ></textarea>
                </div>
            </div>

            <!-- Enfermedades Crónicas -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    🏥 Enfermedades Crónicas
                </label>
                <textarea
                    v-model="formulario.enfermedades_cronicas"
                    rows="2"
                    placeholder="Diabetes, hipertensión, asma..."
                    class="w-full border-gray-300 rounded-md shadow-sm"
                ></textarea>
            </div>

            <!-- Medicamentos Habituales -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    💊 Medicamentos Habituales
                </label>
                <textarea
                    v-model="formulario.medicamentos_habituales"
                    rows="2"
                    placeholder="Medicamentos que toma regularmente..."
                    class="w-full border-gray-300 rounded-md shadow-sm"
                ></textarea>
            </div>

            <!-- Antecedentes -->
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <h4 class="font-bold mb-3">📚 Antecedentes</h4>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Quirúrgicos
                        </label>
                        <textarea
                            v-model="formulario.antecedentes_quirurgicos"
                            rows="2"
                            placeholder="Cirugías previas..."
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Familiares
                        </label>
                        <textarea
                            v-model="formulario.antecedentes_familiares"
                            rows="2"
                            placeholder="Enfermedades familiares..."
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Personales
                        </label>
                        <textarea
                            v-model="formulario.antecedentes_personales"
                            rows="2"
                            placeholder="Historial personal..."
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Datos Físicos -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Peso Habitual (kg)
                    </label>
                    <input
                        v-model="formulario.peso_habitual"
                        type="number"
                        step="0.01"
                        placeholder="70.5"
                        class="w-full border-gray-300 rounded-md shadow-sm"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Estatura (m)
                    </label>
                    <input
                        v-model="formulario.estatura"
                        type="number"
                        step="0.01"
                        placeholder="1.75"
                        class="w-full border-gray-300 rounded-md shadow-sm"
                    />
                </div>
            </div>

            <!-- Hábitos -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    🚬 Hábitos
                </label>
                <textarea
                    v-model="formulario.habitos"
                    rows="2"
                    placeholder="Tabaquismo, consumo de alcohol, ejercicio..."
                    class="w-full border-gray-300 rounded-md shadow-sm"
                ></textarea>
            </div>

            <!-- Vacunas -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    💉 Vacunas
                </label>
                <textarea
                    v-model="formulario.vacunas"
                    rows="2"
                    placeholder="Esquema de vacunación..."
                    class="w-full border-gray-300 rounded-md shadow-sm"
                ></textarea>
            </div>

            <!-- Transfusiones Previas -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    🩸 Transfusiones Previas
                </label>
                <textarea
                    v-model="formulario.transfusiones_previas"
                    rows="2"
                    placeholder="Historial de transfusiones..."
                    class="w-full border-gray-300 rounded-md shadow-sm"
                ></textarea>
            </div>

            <!-- Hospitalizaciones Previas -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    🏥 Hospitalizaciones Previas
                </label>
                <textarea
                    v-model="formulario.hospitalizaciones_previas"
                    rows="2"
                    placeholder="Historial de hospitalizaciones..."
                    class="w-full border-gray-300 rounded-md shadow-sm"
                ></textarea>
            </div>

            <!-- Notas Importantes -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    📝 Notas Importantes
                </label>
                <textarea
                    v-model="formulario.notas_importantes"
                    rows="2"
                    placeholder="Información relevante adicional..."
                    class="w-full border-gray-300 rounded-md shadow-sm"
                ></textarea>
            </div>

            <!-- Botones -->
            <div class="flex gap-4">
                <button
                    type="submit"
                    class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold"
                >
                    💾 Guardar Cambios
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
import { reactive, watch } from 'vue';

const props = defineProps({
    historial: Object,
});

const emit = defineEmits(['guardar', 'cancelar']);

const formulario = reactive({
    grupo_sanguineo: '',
    factor_rh: '',
    alergias: '',
    enfermedades_cronicas: '',
    medicamentos_habituales: '',
    antecedentes_quirurgicos: '',
    antecedentes_familiares: '',
    antecedentes_personales: '',
    peso_habitual: '',
    estatura: '',
    habitos: '',
    vacunas: '',
    transfusiones_previas: '',
    hospitalizaciones_previas: '',
    notas_importantes: '',
});

// Cargar datos del historial cuando cambia
watch(
    () => props.historial,
    (historial) => {
        if (historial) {
            Object.keys(formulario).forEach((key) => {
                formulario[key] = historial[key] || '';
            });
        }
    },
    { immediate: true }
);
</script>

