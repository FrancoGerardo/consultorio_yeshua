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

                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">

                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                        <div class="text-gray-600 text-sm">Total del Día</div>

                        <div class="text-3xl font-bold text-gray-800">{{ estadisticas.total_citas_dia }}</div>

                    </div>

                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                        <div class="text-gray-600 text-sm">Por Llegar</div>

                        <div class="text-3xl font-bold text-blue-600">{{ estadisticas.programadas }}</div>

                    </div>

                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                        <div class="text-gray-600 text-sm">En Espera</div>

                        <div class="text-3xl font-bold text-yellow-600">{{ estadisticas.en_espera }}</div>

                    </div>

                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                        <div class="text-gray-600 text-sm">En Atención</div>

                        <div class="text-3xl font-bold text-red-600">

                            {{ estadisticas.en_atencion ? '1' : '0' }}

                        </div>

                    </div>

                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                        <div class="text-gray-600 text-sm">Atendidas</div>

                        <div class="text-3xl font-bold text-green-600">{{ estadisticas.atendidas }}</div>

                    </div>

                </div>



                <!-- Por llegar (solo informativo) -->

                <div v-if="fichas_programadas.length > 0" class="bg-white shadow-xl sm:rounded-lg p-6 mb-6">

                    <h3 class="text-lg font-semibold mb-1">📅 Programadas — Por llegar</h3>

                    <p class="text-sm text-gray-500 mb-4">

                        Estos pacientes aún no hicieron check-in en recepción. El médico podrá atenderlos cuando pasen a sala de espera.

                    </p>

                    <div class="space-y-3">

                        <div

                            v-for="ficha in fichas_programadas"

                            :key="'prog-' + ficha.id"

                            class="p-4 border-2 border-blue-200 bg-blue-50 rounded-lg opacity-90"

                        >

                            <div class="flex items-center justify-between">

                                <div class="flex items-center gap-3">

                                    <span class="text-2xl">🔵</span>

                                    <div>

                                        <div class="font-bold text-lg">{{ ficha.cliente.usuario.persona.nombre_completo }}</div>

                                        <div class="text-sm text-gray-600">

                                            Hora cita: {{ formatearHora(ficha.hora) }} |

                                            {{ ficha.servicio.nombre }} |

                                            Sala: {{ ficha.sala?.numero || 'Sin asignar' }}

                                        </div>

                                    </div>

                                </div>

                                <span class="px-3 py-1 rounded-full text-sm font-semibold bg-blue-600 text-white">

                                    {{ estadoTexto(ficha.estado) }}

                                </span>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- Cola atendible -->

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                    <h3 class="text-lg font-semibold mb-4">🩺 Listos para Atender</h3>



                    <div v-if="fichas_atendibles.length === 0" class="text-center py-8 text-gray-500">

                        <p>No hay pacientes en sala de espera.</p>

                        <p class="text-sm mt-2">Recepción debe registrar la llegada del paciente.</p>

                    </div>



                    <div v-else class="space-y-3">

                        <div

                            v-for="ficha in fichas_atendibles"

                            :key="ficha.id"

                            :class="[

                                'p-4 border-2 rounded-lg transition-all cursor-pointer hover:shadow-md',

                                ficha.estado === 'EN_ATENCION' ? 'border-red-500 bg-red-50' : 'border-yellow-500 bg-yellow-50'

                            ]"

                            @click="atenderPaciente(ficha)"

                        >

                            <div class="flex items-center justify-between">

                                <div class="flex-1">

                                    <div class="flex items-center gap-3">

                                        <div class="text-2xl">

                                            {{ ficha.estado === 'EN_ATENCION' ? '🔴' : '🟡' }}

                                        </div>

                                        <div>

                                            <div class="font-bold text-lg">

                                                {{ ficha.cliente.usuario.persona.nombre_completo }}

                                            </div>

                                            <div class="text-sm text-gray-600">

                                                Hora cita: {{ formatearHora(ficha.hora) }} |

                                                {{ ficha.servicio.nombre }} |

                                                Sala: {{ ficha.sala?.numero || 'Sin asignar' }}

                                            </div>

                                            <p
                                                v-if="ficha.estado === 'EN_ESPERA' && esAntesDeHoraCita(ficha.fecha, ficha.hora)"
                                                class="text-xs text-amber-700 mt-1"
                                            >
                                                ⏰ Cita programada a las {{ formatearHora(ficha.hora) }}
                                                (faltan ~{{ minutosAntesDeCita(ficha.fecha, ficha.hora) }} min)
                                            </p>

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

                                            ficha.estado === 'EN_ATENCION' ? 'bg-red-600 text-white' : 'bg-yellow-600 text-white'

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

import {
    formatearHoraCita,
    minutosAntesDeCita,
    esAntesDeHoraCita,
    compararHoraCita,
} from '@/utils/formatearHora';



const props = defineProps({

    fichas_programadas: Array,

    fichas_atendibles: Array,

    estadisticas: Object,

    medico: Object,

});



const mostrarModalAtencion = ref(false);

const fichaSeleccionada = ref(null);

const procesandoAtencion = ref(false);



const formatearHora = formatearHoraCita;



const atenderPaciente = async (ficha) => {

    if (procesandoAtencion.value) {

        return;

    }



    if (ficha.estado !== 'EN_ATENCION') {

        const pacientesAnteriores = props.fichas_atendibles.filter(

            (otra) => otra.estado === 'EN_ESPERA'

                && otra.id !== ficha.id

                && compararHoraCita(otra, ficha) < 0,

        );

        if (pacientesAnteriores.length > 0) {

            const nombres = pacientesAnteriores

                .map((p) => p.cliente.usuario.persona.nombre_completo)

                .join(', ');

            const confirmarCola = window.confirm(

                `Hay pacientes con cita anterior en cola:\n${nombres}\n\n`

                + '¿Atender a este paciente de todas formas?',

            );

            if (!confirmarCola) {

                return;

            }

        }



        const minutosTemprano = minutosAntesDeCita(ficha.fecha, ficha.hora);

        if (minutosTemprano > 0) {

            const confirmarTemprano = window.confirm(

                `La cita está programada a las ${formatearHora(ficha.hora)} `

                + `(faltan ~${minutosTemprano} min).\n\n`

                + '¿Iniciar atención ahora?',

            );

            if (!confirmarTemprano) {

                return;

            }

        }

    }



    procesandoAtencion.value = true;



    try {

        if (ficha.estado !== 'EN_ATENCION') {

            const response = await axios.post(route('consultorio.iniciar', ficha.id));

            if (!response.data.success) {

                throw new Error(response.data.message || 'No se pudo iniciar la atención');

            }

        }



        fichaSeleccionada.value = ficha;

        mostrarModalAtencion.value = true;

    } catch (error) {

        const mensaje = error.response?.data?.message || error.message || 'Error al iniciar la atención';

        alert(mensaje);

    } finally {

        procesandoAtencion.value = false;

    }

};



const cerrarModal = () => {

    mostrarModalAtencion.value = false;

    fichaSeleccionada.value = null;

    window.location.reload();

};



const handleConsultaGuardada = () => {

    cerrarModal();

};



const estadoTexto = (estado) => {

    const estados = {

        EN_ATENCION: 'En Atención',

        EN_ESPERA: 'En Espera',

        PAGADA_COMPLETA: 'Programada',

        ANTICIPO_PAGADO: 'Anticipo pagado',

        CONFIRMADA: 'Confirmada',

        ATENDIDA: 'Atendida',

    };

    return estados[estado] || estado;

};

</script>


