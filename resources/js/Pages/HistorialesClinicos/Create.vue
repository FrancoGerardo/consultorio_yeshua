<template>
    <AppLayout title="Crear Historial Clínico">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📋 Crear Nuevo Historial Clínico
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                    
                    <form @submit.prevent="guardar">
                        
                        <!-- Selección de Paciente -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Seleccione el Paciente <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.cliente_id"
                                required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            >
                                <option value="">-- Seleccione un paciente --</option>
                                <option
                                    v-for="cliente in clientes"
                                    :key="cliente.usuario_id"
                                    :value="cliente.usuario_id"
                                >
                                    {{ cliente.usuario.persona.nombre_completo }} - DNI: {{ cliente.usuario.persona.dni }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                Solo se muestran pacientes sin historial clínico
                            </p>
                        </div>

                        <!-- Información Crítica -->
                        <div class="bg-red-50 p-4 rounded-lg mb-6">
                            <h3 class="font-bold text-red-900 mb-4">🩸 Información Crítica</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Grupo Sanguíneo <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.grupo_sanguineo"
                                        required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    >
                                        <option value="">-- Seleccione --</option>
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
                                        v-model="form.factor_rh"
                                        required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    >
                                        <option value="">-- Seleccione --</option>
                                        <option value="Positivo">Positivo (+)</option>
                                        <option value="Negativo">Negativo (-)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Alergias -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                🚨 Alergias
                            </label>
                            <textarea
                                v-model="form.alergias"
                                rows="3"
                                placeholder="Describa las alergias del paciente (medicamentos, alimentos, etc.)"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            ></textarea>
                        </div>

                        <!-- Enfermedades Crónicas -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                🏥 Enfermedades Crónicas
                            </label>
                            <textarea
                                v-model="form.enfermedades_cronicas"
                                rows="3"
                                placeholder="Hipertensión, diabetes, asma, etc."
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            ></textarea>
                        </div>

                        <!-- Medicamentos Habituales -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                💊 Medicamentos Habituales
                            </label>
                            <textarea
                                v-model="form.medicamentos_habituales"
                                rows="3"
                                placeholder="Medicamentos que toma regularmente con dosis"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            ></textarea>
                        </div>

                        <!-- Antecedentes -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="font-bold text-gray-900 mb-4">📚 Antecedentes</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Antecedentes Quirúrgicos
                                    </label>
                                    <textarea
                                        v-model="form.antecedentes_quirurgicos"
                                        rows="2"
                                        placeholder="Cirugías previas..."
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Antecedentes Familiares
                                    </label>
                                    <textarea
                                        v-model="form.antecedentes_familiares"
                                        rows="2"
                                        placeholder="Enfermedades de padres, hermanos..."
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Antecedentes Personales
                                    </label>
                                    <textarea
                                        v-model="form.antecedentes_personales"
                                        rows="2"
                                        placeholder="Historial médico personal..."
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Datos Físicos -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Peso Habitual (kg)
                                </label>
                                <input
                                    v-model="form.peso_habitual"
                                    type="number"
                                    step="0.01"
                                    placeholder="70.5"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Estatura (m)
                                </label>
                                <input
                                    v-model="form.estatura"
                                    type="number"
                                    step="0.01"
                                    placeholder="1.75"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                />
                            </div>
                        </div>

                        <!-- Notas Importantes -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                📝 Notas Importantes
                            </label>
                            <textarea
                                v-model="form.notas_importantes"
                                rows="3"
                                placeholder="Cualquier información adicional relevante..."
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            ></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="flex gap-4 mt-8">
                            <button
                                type="submit"
                                :disabled="guardando"
                                class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 font-semibold"
                            >
                                <span v-if="!guardando">💾 Guardar Historial</span>
                                <span v-else>⏳ Guardando...</span>
                            </button>

                            <button
                                type="button"
                                @click="$inertia.visit(route('historiales-clinicos.index'))"
                                class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-semibold"
                            >
                                ← Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    clientes: Array,
});

const guardando = ref(false);

const form = reactive({
    cliente_id: '',
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
    notas_importantes: '',
});

const guardar = () => {
    if (!form.cliente_id) {
        alert('Debe seleccionar un paciente');
        return;
    }

    if (!form.grupo_sanguineo || !form.factor_rh) {
        alert('El grupo sanguíneo y factor RH son obligatorios');
        return;
    }

    guardando.value = true;

    router.post(route('historiales-clinicos.store'), form, {
        onSuccess: () => {
            alert('✅ Historial clínico creado exitosamente');
        },
        onError: (errors) => {
            console.error('Errores:', errors);
            alert('❌ Error al crear el historial clínico');
        },
        onFinish: () => {
            guardando.value = false;
        },
    });
};
</script>

