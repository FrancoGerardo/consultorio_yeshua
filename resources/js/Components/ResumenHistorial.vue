<template>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold mb-4">📋 Resumen del Historial</h3>

        <!-- Información Crítica -->
        <div class="bg-red-50 p-4 rounded-lg mb-4">
            <h4 class="font-bold text-red-900 mb-2">🩸 Información Crítica</h4>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <span class="text-sm text-gray-600">Grupo Sanguíneo:</span>
                    <p class="font-bold">{{ historial.grupo_sanguineo }}{{ historial.factor_rh === 'Positivo' ? '+' : '-' }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Alergias:</span>
                    <p class="font-bold" :class="historial.alergias ? 'text-red-600' : 'text-gray-400'">
                        {{ historial.alergias || 'Sin alergias registradas' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Enfermedades Crónicas -->
        <div class="mb-4" v-if="historial.enfermedades_cronicas">
            <h4 class="font-bold text-gray-900 mb-2">🏥 Enfermedades Crónicas</h4>
            <p class="text-sm">{{ historial.enfermedades_cronicas }}</p>
        </div>

        <!-- Medicamentos Habituales -->
        <div class="mb-4" v-if="historial.medicamentos_habituales">
            <h4 class="font-bold text-gray-900 mb-2">💊 Medicamentos Habituales</h4>
            <p class="text-sm">{{ historial.medicamentos_habituales }}</p>
        </div>

        <!-- Antecedentes -->
        <div class="mb-4" v-if="historial.antecedentes_personales || historial.antecedentes_familiares">
            <h4 class="font-bold text-gray-900 mb-2">📚 Antecedentes</h4>
            <div class="text-sm space-y-1">
                <p v-if="historial.antecedentes_personales"><strong>Personales:</strong> {{ historial.antecedentes_personales }}</p>
                <p v-if="historial.antecedentes_familiares"><strong>Familiares:</strong> {{ historial.antecedentes_familiares }}</p>
            </div>
        </div>

        <!-- Datos Físicos -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div v-if="historial.peso_habitual">
                <span class="text-sm text-gray-600">Peso:</span>
                <p class="font-bold">{{ historial.peso_habitual }} kg</p>
            </div>
            <div v-if="historial.estatura">
                <span class="text-sm text-gray-600">Estatura:</span>
                <p class="font-bold">{{ historial.estatura }} m</p>
            </div>
        </div>

        <!-- Notas Importantes -->
        <div v-if="historial.notas_importantes" class="bg-yellow-50 p-3 rounded">
            <h4 class="font-bold text-yellow-900 mb-1">⚠️ Notas Importantes</h4>
            <p class="text-sm">{{ historial.notas_importantes }}</p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    historial: Object,
});
</script>

