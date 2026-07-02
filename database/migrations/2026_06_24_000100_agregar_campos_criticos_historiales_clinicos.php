<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('historiales_clinicos', function (Blueprint $table) {
            // Información crítica médica
            $table->string('grupo_sanguineo', 5)->nullable()->after('cliente_id');
            $table->string('factor_rh', 10)->nullable()->after('grupo_sanguineo');
            
            // Antecedentes médicos completos
            $table->text('antecedentes_quirurgicos')->nullable()->after('enfermedades_cronicas');
            $table->text('antecedentes_familiares')->nullable()->after('antecedentes_quirurgicos');
            $table->text('antecedentes_personales')->nullable()->after('antecedentes_familiares');
            
            // Datos físicos
            $table->decimal('peso_habitual', 5, 2)->nullable()->after('antecedentes_personales');
            $table->decimal('estatura', 5, 2)->nullable()->after('peso_habitual');
            
            // Hábitos
            $table->json('habitos')->nullable()->after('estatura');
            
            // Vacunas y transfusiones
            $table->json('vacunas')->nullable()->after('habitos');
            $table->text('transfusiones_previas')->nullable()->after('vacunas');
            
            // Hospitaliza ciones previas
            $table->text('hospitalizaciones_previas')->nullable()->after('transfusiones_previas');
            
            // Notas adicionales
            $table->text('notas_importantes')->nullable()->after('hospitalizaciones_previas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historiales_clinicos', function (Blueprint $table) {
            $table->dropColumn([
                'grupo_sanguineo',
                'factor_rh',
                'antecedentes_quirurgicos',
                'antecedentes_familiares',
                'antecedentes_personales',
                'peso_habitual',
                'estatura',
                'habitos',
                'vacunas',
                'transfusiones_previas',
                'hospitalizaciones_previas',
                'notas_importantes',
            ]);
        });
    }
};

