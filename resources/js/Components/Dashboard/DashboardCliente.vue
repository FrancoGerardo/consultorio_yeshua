<template>
    <div class="space-y-6">
        
        <!-- Mensaje de Bienvenida -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-8 text-white text-center">
            <h2 class="text-3xl font-bold mb-2">¡Bienvenido a su Panel de Paciente! 👋</h2>
            <p class="text-lg opacity-90">Aquí puede gestionar sus citas y ver su historial médico</p>
        </div>

        <!-- Resumen Rápido -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center gap-4">
                    <div class="text-5xl">📅</div>
                    <div>
                        <p class="text-sm text-gray-600">Total de Citas</p>
                        <p class="text-4xl font-bold text-blue-600">{{ datos.resumen?.total_citas || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center gap-4">
                    <div class="text-5xl">🔔</div>
                    <div>
                        <p class="text-sm text-gray-600">Próxima Ficha</p>
                        <p class="text-lg font-bold text-green-600">
                            {{ datos.resumen?.proxima_cita ? formatearFecha(datos.resumen.proxima_cita.fecha) : 'Sin fichas' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accesos Rápidos -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">⚡ Accesos Rápidos</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <button
                    @click="$inertia.visit(route('cliente.servicios.index'))"
                    class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">🏥</span>
                    <span class="text-sm font-semibold text-gray-700">Servicios</span>
                </button>
                <button
                    @click="$inertia.visit(route('cliente.fichas.index'))"
                    class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">🎫</span>
                    <span class="text-sm font-semibold text-gray-700">Mis Fichas</span>
                </button>
                <button
                    @click="$inertia.visit(route('cliente.pagos.index'))"
                    class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">💳</span>
                    <span class="text-sm font-semibold text-gray-700">Mis Pagos</span>
                </button>
            </div>
        </div>

        <!-- Próximas Fichas -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">📅 Mis Próximas Fichas</h3>
            
            <div v-if="datos.proximas_citas && datos.proximas_citas.length > 0" class="space-y-4">
                <div
                    v-for="cita in datos.proximas_citas"
                    :key="cita.id"
                    class="p-4 border-l-4 border-blue-500 bg-blue-50 rounded-lg"
                >
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-600">{{ formatFecha(cita.fecha) }}</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">
                                Dr(a). {{ cita.medico?.usuario?.persona?.nombre_completo || 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-700 mt-1">
                                📋 {{ cita.servicio?.nombre || 'N/A' }}
                            </p>
                            <div class="mt-2">
                                <span
                                    class="px-3 py-1 rounded text-xs font-semibold"
                                    :class="{
                                        'bg-green-200 text-green-800': cita.estado === 'ATENDIDA',
                                        'bg-blue-200 text-blue-800': cita.estado === 'EN_ATENCION',
                                        'bg-yellow-200 text-yellow-800': cita.estado === 'EN_ESPERA',
                                    }"
                                >
                                    {{ cita.estado }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center">
                                <div class="text-center">
                                    <p class="text-xs text-blue-600">{{ obtenerDia(cita.fecha) }}</p>
                                    <p class="text-2xl font-bold text-blue-700">{{ obtenerNumero(cita.fecha) }}</p>
                                    <p class="text-xs text-blue-600">{{ obtenerMes(cita.fecha) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
                <p class="text-6xl mb-4">📭</p>
                <p class="text-lg">No tiene fichas programadas</p>
                <button
                    @click="$inertia.visit(route('cliente.servicios.index'))"
                    class="mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold"
                >
                    Solicitar Nuevo Servicio
                </button>
            </div>
        </div>

        <!-- Historial Clínico Resumido -->
        <div v-if="datos.historial" class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">📋 Mi Historial Clínico</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-red-50 p-4 rounded-lg">
                    <p class="text-sm font-semibold text-red-900">Grupo Sanguíneo</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ datos.historial.grupo_sanguineo }}{{ datos.historial.factor_rh === 'Positivo' ? '+' : '-' }}
                    </p>
                </div>
                
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <p class="text-sm font-semibold text-yellow-900">Alergias</p>
                    <p class="text-sm text-yellow-800 mt-1">
                        {{ datos.historial.alergias || 'Sin alergias registradas' }}
                    </p>
                </div>
            </div>

            <div v-if="datos.historial.enfermedades_cronicas" class="mt-4 bg-orange-50 p-4 rounded-lg">
                <p class="text-sm font-semibold text-orange-900">Enfermedades Crónicas</p>
                <p class="text-sm text-orange-800 mt-1">{{ datos.historial.enfermedades_cronicas }}</p>
            </div>
        </div>

        <!-- Últimas Consultas -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">📝 Últimas Consultas</h3>
            
            <div v-if="datos.ultimas_consultas && datos.ultimas_consultas.length > 0" class="space-y-3">
                <div
                    v-for="consulta in datos.ultimas_consultas"
                    :key="consulta.id"
                    class="p-4 border border-gray-200 rounded-lg"
                >
                    <div class="flex justify-between items-start mb-2">
                        <p class="font-bold text-gray-900">
                            Dr(a). {{ consulta.medico?.usuario?.persona?.nombre_completo || 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-600">{{ formatearFecha(consulta.fecha) }}</p>
                    </div>
                    <p class="text-sm text-gray-700"><strong>Motivo:</strong> {{ consulta.motivo_consulta }}</p>
                    <p class="text-sm text-gray-700 mt-1" v-if="consulta.diagnostico">
                        <strong>Diagnóstico:</strong> {{ consulta.diagnostico }}
                    </p>
                </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
                <p class="text-5xl mb-2">📭</p>
                <p>No hay consultas registradas</p>
            </div>
        </div>

        <!-- Información de Contacto -->
        <div class="bg-gradient-to-r from-cyan-50 to-blue-50 rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">📞 Información de Contacto</h3>
            <div class="space-y-2">
                <p class="text-gray-700">📍 <strong>Dirección:</strong> [Dirección de Consultorio Medico Yeshua]</p>
                <p class="text-gray-700">📞 <strong>Teléfono:</strong> [Número de contacto]</p>
                <p class="text-gray-700">⏰ <strong>Horario:</strong> Lunes a Viernes 8:00 - 20:00</p>
                <p class="text-gray-700">🚨 <strong>Emergencias:</strong> [Número de emergencias]</p>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    datos: Object,
});

const formatearFecha = (fecha) => {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleDateString('es-BO', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatearFechaCompleta = (fecha) => {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleDateString('es-BO', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const obtenerDia = (fecha) => {
    if (!fecha) return '';
    const dias = ['DOM', 'LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB'];
    return dias[new Date(fecha).getDay()];
};

const obtenerNumero = (fecha) => {
    if (!fecha) return '';
    return new Date(fecha).getDate();
};

const obtenerMes = (fecha) => {
    if (!fecha) return '';
    const meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
    return meses[new Date(fecha).getMonth()];
};
</script>

