<template>
    <AppLayout title="Generar Reportes">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📊 Generar Reportes
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Selector de Tipo de Reporte -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">1. Seleccione el Tipo de Reporte</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button
                            @click="tipoSeleccionado = 'citas'"
                            :class="[
                                'p-4 border-2 rounded-lg transition-all',
                                tipoSeleccionado === 'citas' 
                                    ? 'border-blue-500 bg-blue-50' 
                                    : 'border-gray-300 hover:border-blue-300'
                            ]"
                        >
                            <div class="text-3xl mb-2">📅</div>
                            <div class="font-semibold">Reporte de Citas</div>
                            <div class="text-sm text-gray-600">Listado de citas por fecha y médico</div>
                        </button>

                        <button
                            @click="tipoSeleccionado = 'ingresos'"
                            :class="[
                                'p-4 border-2 rounded-lg transition-all',
                                tipoSeleccionado === 'ingresos' 
                                    ? 'border-green-500 bg-green-50' 
                                    : 'border-gray-300 hover:border-green-300'
                            ]"
                        >
                            <div class="text-3xl mb-2">💰</div>
                            <div class="font-semibold">Reporte de Ingresos</div>
                            <div class="text-sm text-gray-600">Análisis financiero de pagos</div>
                        </button>

                        <button
                            @click="tipoSeleccionado = 'pacientes_medico'"
                            :class="[
                                'p-4 border-2 rounded-lg transition-all',
                                tipoSeleccionado === 'pacientes_medico' 
                                    ? 'border-purple-500 bg-purple-50' 
                                    : 'border-gray-300 hover:border-purple-300'
                            ]"
                        >
                            <div class="text-3xl mb-2">👨‍⚕️</div>
                            <div class="font-semibold">Pacientes por Médico</div>
                            <div class="text-sm text-gray-600">Estadísticas por médico</div>
                        </button>
                    </div>
                </div>

                <!-- Filtros -->
                <div v-if="tipoSeleccionado" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">2. Configure los Filtros</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Fecha inicio -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha Inicio
                            </label>
                            <input
                                type="date"
                                v-model="filtros.fecha_inicio"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            />
                        </div>

                        <!-- Fecha fin -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha Fin
                            </label>
                            <input
                                type="date"
                                v-model="filtros.fecha_fin"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            />
                        </div>

                        <!-- Filtros específicos por tipo -->
                        <div v-if="tipoSeleccionado === 'citas'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Estado de Cita
                            </label>
                            <select
                                v-model="filtros.estado"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            >
                                <option value="">Todos los estados</option>
                                <option value="AGENDADA">Agendada</option>
                                <option value="REALIZADA">Realizada</option>
                                <option value="CANCELADA">Cancelada</option>
                                <option value="NO_ASISTIO">No Asistió</option>
                            </select>
                        </div>

                        <div v-if="tipoSeleccionado === 'ingresos'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Método de Pago
                            </label>
                            <select
                                v-model="filtros.metodo_pago"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            >
                                <option value="">Todos los métodos</option>
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="TARJETA">Tarjeta</option>
                                <option value="TRANSFERENCIA">Transferencia</option>
                                <option value="QR">QR</option>
                            </select>
                        </div>

                        <div v-if="tipoSeleccionado === 'citas' || tipoSeleccionado === 'pacientes_medico'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Médico
                            </label>
                            <select
                                v-model="filtros.medico_id"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            >
                                <option value="">Todos los médicos</option>
                                <option v-for="medico in medicos" :key="medico.id" :value="medico.id">
                                    {{ medico.nombre }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Botones de Generación -->
                <div v-if="tipoSeleccionado" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">3. Genere el Reporte</h3>
                    
                    <!-- Opción de envío por email -->
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="enviarEmail"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            />
                            <span class="ml-2 text-sm text-gray-700">
                                📧 Enviar reporte por correo electrónico
                            </span>
                        </label>
                    </div>

                    <div class="flex gap-4 mb-4">
                        <button
                            @click="generarReporte('pdf')"
                            :disabled="generando"
                            class="flex-1 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg transition-all"
                        >
                            <span v-if="!generando">📄 Generar PDF</span>
                            <span v-else>⏳ Generando...</span>
                        </button>

                        <button
                            @click="generarReporte('excel')"
                            :disabled="generando"
                            class="flex-1 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg transition-all"
                        >
                            <span v-if="!generando">📊 Generar Excel</span>
                            <span v-else>⏳ Generando...</span>
                        </button>
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-600 mb-3">
                            💡 <strong>Generación en segundo plano:</strong> Para reportes grandes, puede programarlo y se generará automáticamente
                        </p>
                        <div class="flex gap-4">
                            <button
                                @click="programarReporte('pdf')"
                                :disabled="generando"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg transition-all text-sm"
                            >
                                ⏰ Programar PDF
                            </button>

                            <button
                                @click="programarReporte('excel')"
                                :disabled="generando"
                                class="flex-1 bg-teal-600 hover:bg-teal-700 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg transition-all text-sm"
                            >
                                ⏰ Programar Excel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Historial de Reportes Generados -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Reportes Generados Recientemente</h3>
                        <button
                            @click="cargarReportesGenerados"
                            class="text-blue-600 hover:text-blue-800"
                        >
                            🔄 Actualizar
                        </button>
                    </div>

                    <div v-if="reportesGenerados.length === 0" class="text-center py-8 text-gray-500">
                        No hay reportes generados aún
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="reporte in reportesGenerados"
                            :key="reporte.id"
                            class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                        >
                            <div class="flex-1">
                                <div class="font-semibold">{{ reporte.nombre }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ formatearFecha(reporte.fecha_generacion) }} - 
                                    Formato: {{ reporte.formato.toUpperCase() }}
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a
                                    :href="'/storage/' + reporte.archivo_path"
                                    target="_blank"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                                >
                                    Descargar
                                </a>
                                <button
                                    @click="eliminarReporte(reporte.id)"
                                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

const tipoSeleccionado = ref('');
const generando = ref(false);
const medicos = ref([]);
const reportesGenerados = ref([]);
const enviarEmail = ref(false);

const filtros = ref({
    fecha_inicio: '',
    fecha_fin: '',
    estado: '',
    metodo_pago: '',
    medico_id: ''
});

// Cargar datos iniciales
onMounted(async () => {
    await cargarMedicos();
    await cargarReportesGenerados();
});

// Cargar lista de médicos
const cargarMedicos = async () => {
    try {
        const response = await axios.get('/reportes/datos/generacion');
        medicos.value = response.data.medicos;
    } catch (error) {
        console.error('Error al cargar médicos:', error);
    }
};

// Cargar reportes generados
const cargarReportesGenerados = async () => {
    try {
        const response = await axios.get('/reportes/generados/listar');
        reportesGenerados.value = response.data.reportes.data;
    } catch (error) {
        console.error('Error al cargar reportes:', error);
    }
};

// Generar reporte
const generarReporte = async (formato) => {
    if (!validarFiltros()) {
        alert('Por favor, seleccione al menos las fechas de inicio y fin');
        return;
    }

    generando.value = true;

    try {
        const endpoint = formato === 'pdf' ? '/reportes/generar/pdf' : '/reportes/generar/excel';
        
        const response = await axios.post(endpoint, {
            tipo: tipoSeleccionado.value,
            filtros: filtros.value
        });

        if (response.data.success) {
            alert('✅ Reporte generado exitosamente');
            
            // Descargar automáticamente
            window.open(response.data.url_descarga, '_blank');
            
            // Actualizar lista
            await cargarReportesGenerados();
        }
    } catch (error) {
        console.error('Error al generar reporte:', error);
        alert('❌ Error al generar el reporte: ' + (error.response?.data?.message || error.message));
    } finally {
        generando.value = false;
    }
};

// Programar reporte (en segundo plano con Job)
const programarReporte = async (formato) => {
    if (!validarFiltros()) {
        alert('Por favor, seleccione al menos las fechas de inicio y fin');
        return;
    }

    generando.value = true;

    try {
        const response = await axios.post('/reportes/programar', {
            tipo: tipoSeleccionado.value,
            formato: formato,
            filtros: filtros.value,
            enviar_email: enviarEmail.value
        });

        if (response.data.success) {
            alert('✅ Reporte programado exitosamente!\n\nSe generará en segundo plano y estará disponible en breve.' + 
                  (enviarEmail.value ? '\n\n📧 Recibirá un email cuando esté listo.' : ''));
            
            // Actualizar lista después de unos segundos
            setTimeout(async () => {
                await cargarReportesGenerados();
            }, 3000);
        }
    } catch (error) {
        console.error('Error al programar reporte:', error);
        alert('❌ Error al programar el reporte: ' + (error.response?.data?.message || error.message));
    } finally {
        generando.value = false;
    }
};

// Eliminar reporte
const eliminarReporte = async (id) => {
    if (!confirm('¿Está seguro de eliminar este reporte?')) {
        return;
    }

    try {
        await axios.delete(`/reportes/generados/${id}`);
        alert('✅ Reporte eliminado exitosamente');
        await cargarReportesGenerados();
    } catch (error) {
        console.error('Error al eliminar reporte:', error);
        alert('❌ Error al eliminar el reporte');
    }
};

// Validar filtros
const validarFiltros = () => {
    return filtros.value.fecha_inicio && filtros.value.fecha_fin;
};

// Formatear fecha
const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleString('es-BO', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

