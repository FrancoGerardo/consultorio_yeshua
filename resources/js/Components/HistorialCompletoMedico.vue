<template>
    <div class="p-6 bg-white max-h-[90vh] overflow-y-auto">
        <div v-if="cargando" class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-600">Cargando historial del paciente...</p>
        </div>

        <div v-else-if="paciente">
            <!-- Header del Paciente -->
            <div class="bg-blue-50 p-4 rounded-lg mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-blue-900">
                            {{ paciente.cliente.usuario.persona.nombre_completo }}
                        </h2>
                        <div class="text-sm text-gray-700 mt-2 space-y-1">
                            <div><strong>DNI:</strong> {{ paciente.cliente.usuario.persona.dni }}</div>
                            <div><strong>Edad:</strong> {{ calcularEdad(paciente.cliente.usuario.persona.fecha_nacimiento) }} años</div>
                            <div><strong>Servicio:</strong> {{ paciente.servicio.nombre }}</div>
                        </div>
                    </div>
                    <button @click="$emit('cerrar')" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="width" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Alertas Médicas -->
            <div v-if="historialClinico" class="space-y-3 mb-6">
                <div v-if="!historialClinico.grupo_sanguineo" class="bg-red-100 border-l-4 border-red-500 p-4">
                    <div class="flex">
                        <div class="text-2xl">⚠️</div>
                        <div class="ml-3">
                            <p class="font-bold text-red-800">GRUPO SANGUÍNEO NO REGISTRADO</p>
                            <p class="text-sm text-red-700">Por favor, actualice esta información crítica</p>
                        </div>
                    </div>
                </div>

                <div v-if="historialClinico.alergias" class="bg-yellow-100 border-l-4 border-yellow-500 p-4">
                    <div class="flex">
                        <div class="text-2xl">🚨</div>
                        <div class="ml-3">
                            <p class="font-bold text-yellow-800">ALERGIAS REGISTRADAS</p>
                            <p class="text-sm text-yellow-700">{{ historialClinico.alergias }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="tabActivo = 'consulta'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm',
                            tabActivo === 'consulta' 
                                ? 'border-blue-500 text-blue-600' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        ✍️ Nueva Consulta
                    </button>
                    <button
                        @click="tabActivo = 'historial'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm',
                            tabActivo === 'historial' 
                                ? 'border-blue-500 text-blue-600' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
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
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        📅 Consultas Previas ({{ seguimientosPrevios.length }})
                    </button>
                </nav>
            </div>

            <!-- Contenido de Tabs -->
            <div v-show="tabActivo === 'consulta'">
                <FormularioConsulta
                    :ficha-id="fichaId"
                    :paciente="paciente"
                    @consulta-guardada="handleConsultaGuardada"
                />
            </div>

            <div v-show="tabActivo === 'historial'">
                <FormularioHistorial
                    :historial="historialClinico"
                    :cliente-id="paciente.cliente_id"
                    @actualizado="recargarHistorial"
                />
            </div>

            <div v-show="tabActivo === 'consultas'">
                <ConsultasPrevias :seguimientos="seguimientosPrevios" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import FormularioConsulta from '@/Components/FormularioConsulta.vue';
import FormularioHistorial from '@/Components/FormularioHistorial.vue';
import ConsultasPrevias from '@/Components/ConsultasPrevias.vue';

const props = defineProps({
    fichaId: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['consulta-guardada', 'cerrar']);

const cargando = ref(true);
const paciente = ref(null);
const historialClinico = ref(null);
const seguimientosPrevios = ref([]);
const tabActivo = ref('consulta');

onMounted(async () => {
    await cargarDatos();
});

const cargarDatos = async () => {
    try {
        cargando.value = true;
        const response = await axios.get(`/consultorio/historial/${props.fichaId}`);
        
        if (response.data.success) {
            paciente.value = response.data.ficha;
            historialClinico.value = response.data.historial_clinico;
            seguimientosPrevios.value = response.data.seguimientos_previos;
        }
    } catch (error) {
        console.error('Error al cargar historial:', error);
        alert('Error al cargar el historial del paciente');
    } finally {
        cargando.value = false;
    }
};

const recargarHistorial = async () => {
    await cargarDatos();
};

const handleConsultaGuardada = () => {
    emit('consulta-guardada');
};

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
</script>

