<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('concepto', 20)->default('TOTAL')->after('tipo');
        });

        // Agregar constraint para concepto
        DB::statement("
            ALTER TABLE pagos 
            ADD CONSTRAINT pagos_concepto_check 
            CHECK (concepto IN ('ANTICIPO', 'SALDO', 'CUOTA', 'ABONO', 'TOTAL'))
        ");

        // Actualizar pagos existentes
        // Si la ficha tiene solo un pago completo → TOTAL
        // Si tiene múltiples pagos → el primero es ANTICIPO, los demás SALDO
        DB::statement("
            UPDATE pagos p
            SET concepto = CASE
                WHEN (
                    SELECT COUNT(*) 
                    FROM pagos p2 
                    WHERE p2.ficha_id = p.ficha_id AND p2.estado = 'PAGADO'
                ) = 1 THEN 'TOTAL'
                WHEN p.created_at = (
                    SELECT MIN(p3.created_at) 
                    FROM pagos p3 
                    WHERE p3.ficha_id = p.ficha_id AND p3.estado = 'PAGADO'
                ) THEN 'ANTICIPO'
                ELSE 'SALDO'
            END
            WHERE estado = 'PAGADO' AND tipo = 'CONTADO'
        ");

        // Pagos de tipo CUOTA mantienen su concepto como CUOTA
        DB::statement("UPDATE pagos SET concepto = 'CUOTA' WHERE tipo = 'CUOTA'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pagos DROP CONSTRAINT IF EXISTS pagos_concepto_check");
        
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn('concepto');
        });
    }
};

