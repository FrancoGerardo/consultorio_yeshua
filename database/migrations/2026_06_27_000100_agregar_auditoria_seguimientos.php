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
        Schema::table('seguimientos', function (Blueprint $table) {
            // Médico que realizó el seguimiento (auditoría)
            $table->string('medico_id', 50)->nullable()->after('ficha_id');
            
            // Firma digital del médico
            $table->text('firma_digital')->nullable()->after('medicamentos');
            $table->timestamp('fecha_firma')->nullable()->after('firma_digital');
            
            // Código CIE-10 para diagnóstico
            $table->string('codigo_cie10', 20)->nullable()->after('diagnostico');
            
            // Exámenes solicitados
            $table->json('examenes_solicitados')->nullable()->after('tratamiento_prescrito');
            
            // Interconsultas
            $table->json('interconsultas')->nullable()->after('examenes_solicitados');
            
            // Próxima cita
            $table->date('proxima_cita')->nullable()->after('interconsultas');
            $table->text('indicaciones_proxima_cita')->nullable()->after('proxima_cita');
            
            // Auditoría adicional
            $table->string('ip_registro', 45)->nullable()->after('medicamentos');
            $table->string('navegador', 255)->nullable()->after('ip_registro');
            
            // Estado del seguimiento
            $table->string('estado', 20)->default('ACTIVO')->after('tipo');
            
            // Foreign key para médico
            $table->foreign('medico_id')
                ->references('usuario_id')
                ->on('medicos')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seguimientos', function (Blueprint $table) {
            $table->dropForeign(['medico_id']);
            
            $table->dropColumn([
                'medico_id',
                'firma_digital',
                'fecha_firma',
                'codigo_cie10',
                'examenes_solicitados',
                'interconsultas',
                'proxima_cita',
                'indicaciones_proxima_cita',
                'ip_registro',
                'navegador',
                'estado',
            ]);
        });
    }
};

