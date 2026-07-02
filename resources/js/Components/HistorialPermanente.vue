<template>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold mb-4">📝 Historial Permanente (Datos Estáticos)</h3>

        <div class="space-y-6">
            <!-- Grupo Sanguíneo y Factor RH -->
            <div class="bg-red-50 p-4 rounded-lg">
                <h4 class="font-bold text-red-900 mb-3">🩸 Información Sanguínea</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Grupo Sanguíneo</p>
                        <p class="text-xl font-bold">{{ historial.grupo_sanguineo }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Factor RH</p>
                        <p class="text-xl font-bold">{{ historial.factor_rh }}</p>
                    </div>
                </div>
            </div>

            <!-- Alergias -->
            <div v-if="historial.alergias" class="bg-yellow-50 p-4 rounded-lg">
                <h4 class="font-bold text-yellow-900 mb-2">⚠️ Alergias</h4>
                <p class="whitespace-pre-wrap">{{ historial.alergias }}</p>
            </div>

            <!-- Enfermedades Crónicas -->
            <div v-if="historial.enfermedades_cronicas">
                <h4 class="font-bold text-gray-900 mb-2">🏥 Enfermedades Crónicas</h4>
                <p class="whitespace-pre-wrap">{{ historial.enfermedades_cronicas }}</p>
            </div>

            <!-- Medicamentos Habituales -->
            <div v-if="historial.medicamentos_habituales">
                <h4 class="font-bold text-gray-900 mb-2">💊 Medicamentos Habituales</h4>
                <p class="whitespace-pre-wrap">{{ historial.medicamentos_habituales }}</p>
            </div>

            <!-- Antecedentes Quirúrgicos -->
            <div v-if="historial.antecedentes_quirurgicos">
                <h4 class="font-bold text-gray-900 mb-2">🔪 Antecedentes Quirúrgicos</h4>
                <p class="whitespace-pre-wrap">{{ historial.antecedentes_quirurgicos }}</p>
            </div>

            <!-- Antecedentes Familiares -->
            <div v-if="historial.antecedentes_familiares">
                <h4 class="font-bold text-gray-900 mb-2">👨‍👩‍👧‍👦 Antecedentes Familiares</h4>
                <p class="whitespace-pre-wrap">{{ historial.antecedentes_familiares }}</p>
            </div>

            <!-- Antecedentes Personales -->
            <div v-if="historial.antecedentes_personales">
                <h4 class="font-bold text-gray-900 mb-2">📚 Antecedentes Personales</h4>
                <p class="whitespace-pre-wrap">{{ historial.antecedentes_personales }}</p>
            </div>

            <!-- Datos Físicos -->
            <div class="grid grid-cols-2 gap-4">
                <div v-if="historial.peso_habitual" class="bg-blue-50 p-3 rounded">
                    <p class="text-sm text-gray-600">Peso Habitual</p>
                    <p class="text-2xl font-bold">{{ historial.peso_habitual }} kg</p>
                </div>
                <div v-if="historial.estatura" class="bg-blue-50 p-3 rounded">
                    <p class="text-sm text-gray-600">Estatura</p>
                    <p class="text-2xl font-bold">{{ historial.estatura }} m</p>
                </div>
            </div>

            <!-- Hábitos -->
            <div v-if="historial.habitos">
                <h4 class="font-bold text-gray-900 mb-2">🚬 Hábitos</h4>
                <p class="whitespace-pre-wrap">{{ historial.habitos }}</p>
            </div>

            <!-- Vacunas -->
            <div v-if="historial.vacunas">
                <h4 class="font-bold text-gray-900 mb-2">💉 Vacunas</h4>
                <p class="whitespace-pre-wrap">{{ historial.vacunas }}</p>
            </div>

            <!-- Transfusiones Previas -->
            <div v-if="historial.transfusiones_previas">
                <h4 class="font-bold text-gray-900 mb-2">🩸 Transfusiones Previas</h4>
                <p class="whitespace-pre-wrap">{{ historial.transfusiones_previas }}</p>
            </div>

            <!-- Hospitalizaciones Previas -->
            <div v-if="historial.hospitalizaciones_previas">
                <h4 class="font-bold text-gray-900 mb-2">🏥 Hospitalizaciones Previas</h4>
                <p class="whitespace-pre-wrap">{{ historial.hospitalizaciones_previas }}</p>
            </div>

            <!-- Notas Importantes -->
            <div v-if="historial.notas_importantes" class="bg-orange-50 p-4 rounded-lg">
                <h4 class="font-bold text-orange-900 mb-2">📌 Notas Importantes</h4>
                <p class="whitespace-pre-wrap">{{ historial.notas_importantes }}</p>
            </div>

            <!-- Info de creación/actualización -->
            <div class="border-t pt-4 mt-6">
                <div class="text-sm text-gray-600 space-y-1">
                    <p><strong>Creado:</strong> {{ formatFecha(historial.created_at) }}</p>
                    <p><strong>Última actualización:</strong> {{ formatFecha(historial.updated_at) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    historial: Object,
});

const formatFecha = (fecha) => {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleString('es-BO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

