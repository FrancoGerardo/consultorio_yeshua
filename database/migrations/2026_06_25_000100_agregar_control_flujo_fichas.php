<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, eliminar la restricción del check existente
        DB::statement("ALTER TABLE fichas DROP CONSTRAINT IF EXISTS fichas_estado_check");
        
        Schema::table('fichas', function (Blueprint $table) {
            // Cambiar el tipo de columna para permitir más estados
            $table->string('estado', 20)->change();
            
            // Agregar timestamps de control de flujo
            $table->timestamp('fecha_confirmacion')->nullable()->after('estado');
            $table->timestamp('fecha_llegada')->nullable()->after('fecha_confirmacion');
            $table->timestamp('fecha_inicio_atencion')->nullable()->after('fecha_llegada');
            $table->timestamp('fecha_fin_atencion')->nullable()->after('fecha_inicio_atencion');
            
            // Tiempo de espera calculado (en minutos)
            $table->integer('tiempo_espera_minutos')->nullable()->after('fecha_fin_atencion');
            $table->integer('tiempo_atencion_minutos')->nullable()->after('tiempo_espera_minutos');
            
            // Observaciones internas
            $table->text('observaciones_internas')->nullable()->after('motivo_consulta');
        });
        
        // Agregar nueva restricción con más estados
        DB::statement("ALTER TABLE fichas ADD CONSTRAINT fichas_estado_check CHECK (estado IN ('PENDIENTE', 'CONFIRMADA', 'EN_ESPERA', 'EN_ATENCION', 'ATENDIDA', 'CANCELADA', 'NO_ASISTIO'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE fichas DROP CONSTRAINT IF EXISTS fichas_estado_check");
        
        Schema::table('fichas', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_confirmacion',
                'fecha_llegada',
                'fecha_inicio_atencion',
                'fecha_fin_atencion',
                'tiempo_espera_minutos',
                'tiempo_atencion_minutos',
                'observaciones_internas',
            ]);
        });
        
        // Restaurar restricción original
        DB::statement("ALTER TABLE fichas ADD CONSTRAINT fichas_estado_check CHECK (estado IN ('PENDIENTE', 'CONFIRMADA', 'ATENDIDA', 'CANCELADA'))");
    }
};

