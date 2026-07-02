<template>
    <AppLayout :title="`Historial - ${paciente.nombre_completo}`">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📋 Historial Clínico Completo
                </h2>
                <button
                    @click="$inertia.visit(route('historiales-clinicos.index'))"
                    class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
                >
                    ← Volver
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Header del Paciente -->
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-blue-900 mb-2">
                                {{ paciente.nombre_completo }}
                            </h1>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="font-semibold">DNI:</span> {{ paciente.dni }}
                                </div>
                                <div>
                                    <span class="font-semibold">Edad:</span> {{ calcularEdad(paciente.fecha_nacimiento) }} años
                                </div>
                                <div>
                                    <span class="font-semibold">Teléfono:</span> {{ paciente.telefono || 'N/A' }}
                                </div>
                                <div>
                                    <span class="font-semibold">Email:</span> {{ historial.cliente.usuario.email }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-600 mb-2">Completitud del Historial</div>
                            <div class="text-4xl font-bold" :class="[
                                completitud >= 80 ? 'text-green-600' :
                                completitud >= 50 ? 'text-yellow-600' :
                                'text-red-600'
                            ]">
                                {{ completitud }}%
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertas Médicas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Grupo Sanguíneo -->
                    <div :class="[
                        'p-6 rounded-lg shadow',
                        historial.grupo_sanguineo ? 'bg-red-50 border-2 border-red-200' : 'bg-yellow-50 border-2 border-yellow-200'
                    ]">
                        <div class="flex items-center gap-3">
                            <div class="text-4xl">🩸</div>
                            <div>
                                <div class="font-bold text-lg">Grupo Sanguíneo</div>
                                <div v-if="historial.grupo_sanguineo" class="text-2xl font-bold text-red-700">
                                    {{ historial.grupo_sanguineo }}{{ historial.factor_rh }}
                                </div>
                                <div v-else class="text-yellow-700 font-semibold">
                                    ⚠️ NO REGISTRADO - Actualizar urgente
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alergias -->
                    <div :class="[
                        'p-6 rounded-lg shadow',
                        historial.alergias ? 'bg-yellow-50 border-2 border-yellow-300' : 'bg-green-50 border-2 border-green-200'
                    ]">
                        <div class="flex items-center gap-3">
                            <div class="text-4xl">{{ historial.alergias ? '🚨' : '✅' }}</div>
                            <div>
                                <div class="font-bold text-lg">Alergias</div>
                                <div v-if="historial.alergias" class="text-yellow-800">
                                    {{ historial.alergias }}
                                </div>
                                <div v-else class="text-green-700">
                                    Sin alergias registradas
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenido en Tabs -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-8">
                            <button
                                @click="tabActivo = 'resumen'"
                                :class="[
                                    'py-4 px-1 border-b-2 font-medium text-sm',
                                    tabActivo === 'resumen' 
                                        ? 'border-blue-500 text-blue-600' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700'
                                ]"
                            >
                                📊 Resumen
                            </button>
                            <button
                                @click="tabActivo = 'historial'"
                                :class="[
                                    'py-4 px-1 border-b-2 font-medium text-sm',
                                    tabActivo === 'historial' 
                                        ? 'border-blue-500 text-blue-600' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700'
                                ]"
                            >
                                📋 Historial Permanente
                            </button>
                            <button
                                @click="tabActivo = 'consultas'"
                                :class="[
                                    'py-4 px-1 border-b-2 font-medium text-sm',
                                    tabActivo === 'consultas' 
                                        ? 'border-blue-500 text-blue-600' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700'
                                ]"
                            >
                                📅 Consultas ({{ historial.cliente.fichas.length }})
                            </button>
                        </nav>
                    </div>

                    <!-- Contenido de Tabs -->
                    <div v-show="tabActivo === 'resumen'">
                        <ResumenHistorial :historial="historial" />
                    </div>

                    <div v-show="tabActivo === 'historial'">
                        <HistorialPermanente :historial="historial" />
                    </div>

                    <div v-show="tabActivo === 'consultas'">
                        <TimelineConsultas :fichas="historial.cliente.fichas" />
                    </div>
                </div>

                <!-- Acciones -->
                <div class="mt-6 flex gap-4">
                    <button
                        @click="editarHistorial"
                        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold"
                    >
                        ✏️ Editar Historial
                    </button>
                    <button
                        @click="exportarPDF"
                        class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold"
                    >
                        📄 Exportar PDF
                    </button>
                    <button
                        @click="$inertia.visit(route('historiales-clinicos.index'))"
                        class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-semibold"
                    >
                        ← Volver a la Lista
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ResumenHistorial from '@/Components/ResumenHistorial.vue';
import HistorialPermanente from '@/Components/HistorialPermanente.vue';
import TimelineConsultas from '@/Components/TimelineConsultas.vue';

const props = defineProps({
    historial: Object,
    completitud: Number,
});

const tabActivo = ref('resumen');

const paciente = computed(() => props.historial.cliente.usuario.persona);

const calcularEdad = (fechaNacimiento) => {
    if (!fechaNacimiento) return 'N/A';
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }
    return edad;
};

const editarHistorial = () => {
    router.visit(route('historiales-clinicos.edit', props.historial.id));
};

const exportarPDF = () => {
    window.open(route('historiales-clinicos.exportar-pdf', props.historial.id), '_blank');
};
</script>

