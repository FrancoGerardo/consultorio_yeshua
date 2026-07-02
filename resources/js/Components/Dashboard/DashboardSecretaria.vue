<template>
    <div class="space-y-6">
        
        <!-- Resumen Rápido -->
        <div class="bg-gradient-to-r from-pink-50 to-purple-50 rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">📊 Resumen del Día</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg p-4">
                    <p class="text-sm text-gray-600">Citas Confirmadas</p>
                    <p class="text-3xl font-bold text-green-600">{{ datos.resumen?.citas_confirmadas || 0 }}</p>
                </div>
                <div class="bg-white rounded-lg p-4">
                    <p class="text-sm text-gray-600">Pendientes de Confirmación</p>
                    <p class="text-3xl font-bold text-orange-600">{{ datos.resumen?.citas_pendientes_confirmacion || 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Accesos Rápidos -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">⚡ Accesos Rápidos</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button
                    @click="$inertia.visit(route('fichas.crear'))"
                    class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">➕</span>
                    <span class="text-sm font-semibold text-gray-700">Nueva Ficha</span>
                </button>
                <button
                    @click="$inertia.visit(route('fichas.index'))"
                    class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">🎫</span>
                    <span class="text-sm font-semibold text-gray-700">Fichas</span>
                </button>
                <button
                    @click="$inertia.visit(route('pagos.index'))"
                    class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">💳</span>
                    <span class="text-sm font-semibold text-gray-700">Pagos</span>
                </button>
                <button
                    @click="$inertia.visit(route('usuarios.index'))"
                    class="flex flex-col items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">👥</span>
                    <span class="text-sm font-semibold text-gray-700">Pacientes</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Pacientes en Sala de Espera -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold mb-4">⏳ Sala de Espera</h3>
                
                <div v-if="datos.pacientes_espera && datos.pacientes_espera.length > 0" class="space-y-3">
                    <div
                        v-for="ficha in datos.pacientes_espera"
                        :key="ficha.id"
                        class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                <span class="text-xl font-bold text-orange-600">{{ ficha.numero_ficha }}</span>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">
                                    {{ ficha.cliente?.usuario?.persona?.nombre_completo || 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Dr(a). {{ ficha.medico?.usuario?.persona?.nombre_completo || 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-semibold">
                            EN ESPERA
                        </span>
                    </div>
                </div>

                <div v-else class="text-center py-8 text-gray-500">
                    <p class="text-5xl mb-2">✅</p>
                    <p>No hay pacientes en espera</p>
                </div>
            </div>

            <!-- Próximas Fichas Pendientes -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold mb-4">🔔 Próximas Fichas Pendientes</h3>
                
                <div v-if="datos.proximas_citas && datos.proximas_citas.length > 0" class="space-y-3">
                    <div
                        v-for="cita in datos.proximas_citas"
                        :key="cita.id"
                        class="p-3 border-l-4 border-blue-500 bg-blue-50 rounded"
                    >
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-bold text-gray-900">
                                    {{ cita.cliente?.usuario?.persona?.nombre_completo || 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Dr(a). {{ cita.medico?.usuario?.persona?.nombre_completo || 'N/A' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-blue-700">
                                    {{ formatearHora(cita.hora) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ calcularTiempoRestante(cita.fecha) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-8 text-gray-500">
                    <p class="text-5xl mb-2">📭</p>
                    <p>No hay fichas pendientes</p>
                </div>
            </div>
        </div>

        <!-- Fichas Completas del Día -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">📅 Fichas de Hoy</h3>
            
            <div v-if="datos.citas_hoy && datos.citas_hoy.length > 0" class="space-y-2">
                <div
                    v-for="cita in datos.citas_hoy"
                    :key="cita.id"
                    class="flex items-center justify-between p-3 border-l-4 rounded"
                    :class="{
                        'border-green-500 bg-green-50': cita.estado === 'ATENDIDO',
                        'border-yellow-500 bg-yellow-50': cita.estado === 'PENDIENTE',
                        'border-red-500 bg-red-50': cita.estado === 'CANCELADO',
                    }"
                >
                    <div class="flex items-center gap-4">
                        <div class="text-center">
                            <p class="text-xs text-gray-600">Hora</p>
                            <p class="font-bold text-gray-900">{{ formatearHora(cita.hora) }}</p>
                        </div>
                        <div class="border-l pl-4">
                            <p class="font-bold text-gray-900">
                                {{ cita.cliente?.usuario?.persona?.nombre_completo || 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Dr(a). {{ cita.medico?.usuario?.persona?.nombre_completo || 'N/A' }} - 
                                {{ cita.servicio?.nombre || 'N/A' }}
                            </p>
                        </div>
                    </div>
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

            <div v-else class="text-center py-8 text-gray-500">
                <p class="text-5xl mb-2">📭</p>
                <p>No hay fichas para hoy</p>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    datos: Object,
});

const formatearHora = (fechaHora) => {
    if (!fechaHora) return 'N/A';
    return new Date(fechaHora).toLocaleTimeString('es-BO', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const calcularTiempoRestante = (fecha) => {
    if (!fecha) return 'Próxima';
    
    const ahora = new Date();
    const ficha = new Date(fecha);
    const diff = ficha - ahora;
    
    if (diff < 0) return 'Hoy';
    
    const dias = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    if (dias === 0) {
        return 'Hoy';
    } else if (dias === 1) {
        return 'Mañana';
    } else {
        return `En ${dias} días`;
    }
};
</script>

