<template>
    <AppLayout title="Consultorio Médico">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🏥 Consultorio Médico - Dr(a). {{ medico.usuario.persona.nombre_completo }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Estadísticas del Día -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <div class="text-gray-600">Total del Día</div>
                        <div class="text-3xl font-bold text-blue-600">{{ estadisticas.total_citas_dia }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <div class="text-gray-600">En Espera</div>
                        <div class="text-3xl font-bold text-yellow-600">{{ estadisticas.en_espera }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <div class="text-gray-600">Atendidas</div>
                        <div class="text-3xl font-bold text-green-600">{{ estadisticas.atendidas }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <div class="text-gray-600">En Atención</div>
                        <div class="text-3xl font-bold text-red-600">
                            {{ estadisticas.en_atencion ? '1' : '0' }}
                        </div>
                    </div>
                </div>

                <!-- Cola de Pacientes -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">📋 Cola de Pacientes del Día</h3>

                    <div v-if="fichas.length === 0" class="text-center py-8 text-gray-500">
                        No hay pacientes en cola para hoy
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="ficha in fichas"
                            :key="ficha.id"
                            :class="[
                                'p-4 border-2 rounded-lg transition-all cursor-pointer',
                                ficha.estado === 'EN_ATENCION' ? 'border-red-500 bg-red-50' :
                                ficha.estado === 'EN_ESPERA' ? 'border-yellow-500 bg-yellow-50' :
                                'border-gray-300 hover:border-blue-300'
                            ]"
                            @click="atenderPaciente(ficha)"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <div class="text-2xl">
                                            {{ ficha.estado === 'EN_ATENCION' ? '🔴' : 
                                               ficha.estado === 'EN_ESPERA' ? '🟡' : '🟢' }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-lg">
                                                {{ ficha.cliente.usuario.persona.nombre_completo }}
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                Hora: {{ formatearHora(ficha.hora) }} | 
                                                Servicio: {{ ficha.servicio.nombre }} |
                                                Sala: {{ ficha.sala?.numero || 'Sin asignar' }}
                                            </div>
                                            <div v-if="ficha.motivo_consulta" class="text-sm text-gray-700 mt-1">
                                                <strong>Motivo:</strong> {{ ficha.motivo_consulta }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end gap-2">
                                    <span
                                        :class="[
                                            'px-3 py-1 rounded-full text-sm font-semibold',
                                            ficha.estado === 'EN_ATENCION' ? 'bg-red-600 text-white' :
                                            ficha.estado === 'EN_ESPERA' ? 'bg-yellow-600 text-white' :
                                            'bg-blue-600 text-white'
                                        ]"
                                    >
                                        {{ estadoTexto(ficha.estado) }}
                                    </span>

                                    <div v-if="ficha.tiempo_espera_minutos" class="text-xs text-gray-600">
                                        Esperó: {{ ficha.tiempo_espera_minutos }} min
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Atención del Paciente -->
        <Modal :show="mostrarModalAtencion" @close="cerrarModal" max-width="6xl">
            <HistorialCompletoMedico
                v-if="fichaSeleccionada"
                :ficha-id="fichaSeleccionada.id"
                @consulta-guardada="handleConsultaGuardada"
                @cerrar="cerrarModal"
            />
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import HistorialCompletoMedico from '@/Components/HistorialCompletoMedico.vue';
import axios from 'axios';

const props = defineProps({
    fichas: Array,
    estadisticas: Object,
    medico: Object,
});

const mostrarModalAtencion = ref(false);
const fichaSeleccionada = ref(null);

const atenderPaciente = (ficha) => {
    fichaSeleccionada.value = ficha;
    mostrarModalAtencion.value = true;
};

const cerrarModal = () => {
    mostrarModalAtencion.value = false;
    fichaSeleccionada.value = null;
    
    // Recargar página para actualizar cola
    window.location.reload();
};

const handleConsultaGuardada = () => {
    cerrarModal();
};

const formatearHora = (hora) => {
    return hora.substring(0, 5);
};

const estadoTexto = (estado) => {
    const estados = {
        'EN_ATENCION': 'En Atención',
        'EN_ESPERA': 'En Espera',
        'CONFIRMADA': 'Confirmada',
        'ATENDIDA': 'Atendida',
    };
    return estados[estado] || estado;
};
</script>

