<template>
    <AppLayout title="Historiales Clínicos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📋 Gestión de Historiales Clínicos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Barra de Búsqueda y Filtros -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Búsqueda -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                🔍 Buscar Paciente
                            </label>
                            <input
                                v-model="filtrosBusqueda.busqueda"
                                @input="debounceSearch"
                                type="text"
                                placeholder="Nombre, apellido o DNI..."
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            />
                        </div>

                        <!-- Filtro Completitud -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Estado
                            </label>
                            <select
                                v-model="filtrosBusqueda.completitud"
                                @change="aplicarFiltros"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            >
                                <option value="">Todos</option>
                                <option value="completo">Completos</option>
                                <option value="incompleto">Incompletos</option>
                            </select>
                        </div>

                        <!-- Filtros Rápidos -->
                        <div class="flex flex-col gap-2">
                            <label class="flex items-center">
                                <input
                                    v-model="filtrosBusqueda.sin_grupo_sanguineo"
                                    @change="aplicarFiltros"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600"
                                />
                                <span class="ml-2 text-sm">Sin grupo sanguíneo</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    v-model="filtrosBusqueda.con_alergias"
                                    @change="aplicarFiltros"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-yellow-600"
                                />
                                <span class="ml-2 text-sm">Con alergias</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Lista de Historiales -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">
                            Historiales Clínicos ({{ historiales.total }})
                        </h3>
                        <button
                            @click="$inertia.visit(route('historiales-clinicos.create'))"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                        >
                            + Nuevo Historial
                        </button>
                    </div>

                    <!-- Tabla -->
                    <div v-if="historiales.data.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Paciente
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        🩸 Grupo Sanguíneo
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        ⚠️ Alertas
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Última Consulta
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Completitud
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="historial in historiales.data"
                                    :key="historial.id"
                                    class="hover:bg-gray-50"
                                >
                                    <!-- Paciente -->
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ historial.cliente.usuario.persona.nombre_completo }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            DNI: {{ historial.cliente.usuario.persona.dni }} |
                                            {{ calcularEdad(historial.cliente.usuario.persona.fecha_nacimiento) }} años
                                        </div>
                                    </td>

                                    <!-- Grupo Sanguíneo -->
                                    <td class="px-6 py-4">
                                        <span
                                            v-if="historial.grupo_sanguineo"
                                            class="px-3 py-1 bg-red-100 text-red-800 rounded-full font-semibold text-sm"
                                        >
                                            {{ historial.grupo_sanguineo }}{{ historial.factor_rh }}
                                        </span>
                                        <span
                                            v-else
                                            class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm"
                                        >
                                            ⚠️ Sin registrar
                                        </span>
                                    </td>

                                    <!-- Alertas -->
                                    <td class="px-6 py-4">
                                        <div v-if="historial.alergias" class="flex items-center gap-2">
                                            <span class="text-2xl">🚨</span>
                                            <div class="text-sm">
                                                <div class="font-semibold text-yellow-800">Alergias</div>
                                                <div class="text-gray-600 truncate max-w-xs">
                                                    {{ historial.alergias }}
                                                </div>
                                            </div>
                                        </div>
                                        <span v-else class="text-gray-400 text-sm">Sin alergias</span>
                                    </td>

                                    <!-- Última Consulta -->
                                    <td class="px-6 py-4">
                                        <div v-if="historial.ultima_consulta" class="text-sm">
                                            <div class="text-gray-900">
                                                {{ formatearFecha(historial.ultima_consulta.fecha) }}
                                            </div>
                                            <div class="text-gray-500">
                                                {{ historial.ultima_consulta.servicio?.nombre }}
                                            </div>
                                        </div>
                                        <span v-else class="text-gray-400 text-sm">Sin consultas</span>
                                    </td>

                                    <!-- Completitud -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                                <div
                                                    :class="[
                                                        'h-2 rounded-full',
                                                        historial.completitud >= 80 ? 'bg-green-500' :
                                                        historial.completitud >= 50 ? 'bg-yellow-500' :
                                                        'bg-red-500'
                                                    ]"
                                                    :style="{ width: historial.completitud + '%' }"
                                                ></div>
                                            </div>
                                            <span class="text-sm font-medium">{{ historial.completitud }}%</span>
                                        </div>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <button
                                                @click="verCompleto(historial.id)"
                                                class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                                                title="Ver historial completo"
                                            >
                                                👁️ Ver
                                            </button>
                                            <button
                                                @click="editar(historial.id)"
                                                class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm"
                                                title="Editar"
                                            >
                                                ✏️
                                            </button>
                                            <button
                                                @click="exportarPDF(historial.id)"
                                                class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                                                title="Exportar PDF"
                                            >
                                                📄
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Paginación -->
                        <div class="mt-4 flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                Mostrando {{ historiales.from }} a {{ historiales.to }} de {{ historiales.total }} resultados
                            </div>
                            <div class="flex gap-2">
                                <button
                                    v-for="link in historiales.links"
                                    :key="link.label"
                                    @click="cambiarPagina(link.url)"
                                    :disabled="!link.url"
                                    :class="[
                                        'px-3 py-2 rounded',
                                        link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700',
                                        !link.url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-500 hover:text-white'
                                    ]"
                                    v-html="link.label"
                                ></button>
                            </div>
                        </div>
                    </div>

                    <!-- Sin resultados -->
                    <div v-else class="text-center py-12 text-gray-500">
                        <div class="text-4xl mb-4">📋</div>
                        <p class="text-lg">No se encontraron historiales clínicos</p>
                        <p class="text-sm mt-2">Intenta ajustar los filtros o crear un nuevo historial</p>
                    </div>
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
    historiales: Object,
    filtros: Object,
    contadorVisitas: Number,
});

const filtrosBusqueda = reactive({
    busqueda: props.filtros?.busqueda || '',
    sin_grupo_sanguineo: props.filtros?.sin_grupo_sanguineo || false,
    con_alergias: props.filtros?.con_alergias || false,
    completitud: props.filtros?.completitud || '',
});

let searchTimeout = null;

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        aplicarFiltros();
    }, 500);
};

const aplicarFiltros = () => {
    router.get(route('historiales-clinicos.index'), filtrosBusqueda, {
        preserveState: true,
        preserveScroll: true,
    });
};

const cambiarPagina = (url) => {
    if (!url) return;
    router.get(url, {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const verCompleto = (id) => {
    router.visit(route('historiales-clinicos.ver-completo', id));
};

const editar = (id) => {
    router.visit(route('historiales-clinicos.edit', id));
};

const exportarPDF = (id) => {
    window.open(route('historiales-clinicos.exportar-pdf', id), '_blank');
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

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-BO', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
};
</script>
