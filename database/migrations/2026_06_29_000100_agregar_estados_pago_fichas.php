<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Actualizar constraint de estado para incluir nuevos estados de pago
        DB::statement("ALTER TABLE fichas DROP CONSTRAINT IF EXISTS fichas_estado_check");
        DB::statement("
            ALTER TABLE fichas 
            ADD CONSTRAINT fichas_estado_check 
            CHECK (estado IN (
                'PENDIENTE_PAGO',
                'ANTICIPO_PAGADO',
                'PAGADA_COMPLETA',
                'EN_ESPERA',
                'EN_ATENCION',
                'ATENDIDA',
                'CANCELADA',
                'NO_ASISTIO',
                'PENDIENTE',
                'CONFIRMADA'
            ))
        ");

        // Actualizar fichas existentes
        // Las que están PENDIENTE sin pagos → PENDIENTE_PAGO
        DB::statement("
            UPDATE fichas 
            SET estado = 'PENDIENTE_PAGO' 
            WHERE estado = 'PENDIENTE' 
            AND id NOT IN (SELECT DISTINCT ficha_id FROM pagos WHERE estado = 'PAGADO')
        ");

        // Las que están PENDIENTE con pagos → ANTICIPO_PAGADO o PAGADA_COMPLETA
        DB::statement("
            UPDATE fichas f
            SET estado = CASE 
                WHEN (
                    SELECT COALESCE(SUM(p.monto), 0) 
                    FROM pagos p 
                    WHERE p.ficha_id = f.id AND p.estado = 'PAGADO'
                ) >= (
                    SELECT COALESCE(s.costo, 0) 
                    FROM servicios s 
                    WHERE s.id = f.servicio_id
                ) THEN 'PAGADA_COMPLETA'
                ELSE 'ANTICIPO_PAGADO'
            END
            WHERE estado = 'PENDIENTE' 
            AND id IN (SELECT DISTINCT ficha_id FROM pagos WHERE estado = 'PAGADO')
        ");

        // Las que están CONFIRMADA → PAGADA_COMPLETA
        DB::statement("UPDATE fichas SET estado = 'PAGADA_COMPLETA' WHERE estado = 'CONFIRMADA'");
    }

    public function down(): void
    {
        // Restaurar estados originales
        DB::statement("UPDATE fichas SET estado = 'PENDIENTE' WHERE estado IN ('PENDIENTE_PAGO', 'ANTICIPO_PAGADO')");
        DB::statement("UPDATE fichas SET estado = 'CONFIRMADA' WHERE estado = 'PAGADA_COMPLETA'");

        // Restaurar constraint original
        DB::statement("ALTER TABLE fichas DROP CONSTRAINT IF EXISTS fichas_estado_check");
        DB::statement("
            ALTER TABLE fichas 
            ADD CONSTRAINT fichas_estado_check 
            CHECK (estado IN (
                'PENDIENTE',
                'CONFIRMADA',
                'EN_ESPERA',
                'EN_ATENCION',
                'ATENDIDA',
                'CANCELADA',
                'NO_ASISTIO'
            ))
        ");
    }
};

