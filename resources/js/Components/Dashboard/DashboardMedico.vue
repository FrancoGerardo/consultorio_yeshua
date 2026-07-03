<template>
    <div class="space-y-6">
        
        <!-- Resumen del Médico -->
        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">👨‍⚕️ Mi Resumen del Día</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg p-4">
                    <p class="text-sm text-gray-600">Especialidad</p>
                    <p class="text-2xl font-bold text-blue-600">{{ datos.resumen?.especialidad || 'N/A' }}</p>
                </div>
                <div class="bg-white rounded-lg p-4">
                    <p class="text-sm text-gray-600">Pacientes Atendidos</p>
                    <p class="text-3xl font-bold text-green-600">{{ datos.resumen?.pacientes_atendidos_hoy || 0 }}</p>
                </div>
                <div class="bg-white rounded-lg p-4">
                    <p class="text-sm text-gray-600">Pacientes Pendientes</p>
                    <p class="text-3xl font-bold text-orange-600">{{ datos.resumen?.pacientes_pendientes || 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Acceso Rápido al Consultorio -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white text-center">
            <h3 class="text-2xl font-bold mb-2">🩺 Acceso al Consultorio</h3>
            <p class="mb-4 opacity-90">Gestiona tu cola de pacientes y realiza consultas</p>
            <button
                @click="$inertia.visit(route('consultorio.cola'))"
                class="px-8 py-3 bg-white text-green-600 rounded-lg font-bold hover:bg-gray-100 transition"
            >
                ➡️ Ir al Consultorio
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Cola de Pacientes -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold mb-4">⏳ Cola de Pacientes</h3>
                
                <div v-if="datos.cola_pacientes && datos.cola_pacientes.length > 0" class="space-y-3">
                    <div
                        v-for="ficha in datos.cola_pacientes"
                        :key="ficha.id"
                        class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:shadow-md transition"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-xl font-bold text-blue-600">{{ ficha.numero_ficha }}</span>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">
                                    {{ ficha.cliente?.usuario?.persona?.nombre_completo || 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500">DNI: {{ ficha.cliente?.usuario?.persona?.dni || 'N/A' }}</p>
                            </div>
                        </div>
                        <button
                            @click="$inertia.visit(route('consultorio.cola'))"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-semibold"
                        >
                            Atender
                        </button>
                    </div>
                </div>

                <div v-else class="text-center py-8 text-gray-500">
                    <p class="text-5xl mb-2">✅</p>
                    <p>No hay pacientes en cola</p>
                </div>
            </div>

            <!-- Citas del Día -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold mb-4">📅 Mis Citas de Hoy</h3>
                
                <div v-if="datos.citas_hoy && datos.citas_hoy.length > 0" class="space-y-3">
                    <div
                        v-for="cita in datos.citas_hoy"
                        :key="cita.id"
                        class="p-3 border-l-4 rounded"
                        :class="{
                            'border-green-500 bg-green-50': cita.estado === 'COMPLETADA',
                            'border-yellow-500 bg-yellow-50': cita.estado === 'PENDIENTE',
                            'border-blue-500 bg-blue-50': cita.estado === 'CONFIRMADA',
                        }"
                    >
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-bold text-gray-900">
                                    {{ cita.cliente?.usuario?.persona?.nombre_completo || 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ cita.servicio?.nombre || 'N/A' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-700">
                                    {{ formatearHora(cita.hora) }}
                                </p>
                                <span
                                    class="text-xs px-2 py-1 rounded"
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
                    </div>
                </div>

                <div v-else class="text-center py-8 text-gray-500">
                    <p class="text-5xl mb-2">📭</p>
                    <p>No tienes citas programadas hoy</p>
                </div>
            </div>
        </div>

        <!-- Accesos Rápidos -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">⚡ Accesos Rápidos</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button
                    @click="$inertia.visit(route('consultorio.cola'))"
                    class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">🩺</span>
                    <span class="text-sm font-semibold text-gray-700">Consultorio</span>
                </button>
                <button
                    @click="$inertia.visit(route('historiales-clinicos.index'))"
                    class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">📋</span>
                    <span class="text-sm font-semibold text-gray-700">Historiales</span>
                </button>
                <button
                    @click="$inertia.visit(route('fichas.index'))"
                    class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">🎫</span>
                    <span class="text-sm font-semibold text-gray-700">Fichas</span>
                </button>
                <button
                    @click="$inertia.visit(route('seguimientos.index'))"
                    class="flex flex-col items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition"
                >
                    <span class="text-3xl mb-2">📝</span>
                    <span class="text-sm font-semibold text-gray-700">Seguimientos</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    datos: Object,
});

const formatearHora = (fecha) => {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleTimeString('es-BO', {
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

