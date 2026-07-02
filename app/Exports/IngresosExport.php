<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IngresosExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $pagos;
    protected $estadisticas;

    public function __construct($pagos, $estadisticas)
    {
        $this->pagos = $pagos;
        $this->estadisticas = $estadisticas;
    }

    public function collection()
    {
        return $this->pagos->map(function($pago) {
            return [
                'ID' => $pago->id,
                'Fecha' => $pago->fecha_pago->format('d/m/Y H:i'),
                'Paciente' => $pago->ficha->cliente->usuario->persona->nombre_completo ?? 'N/A',
                'Servicio' => $pago->ficha->servicio->nombre ?? 'N/A',
                'Monto' => number_format($pago->monto, 2),
                'Método' => $pago->metodo_pago,
                'Tipo' => $pago->tipo,
                'Estado' => $pago->estado,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Paciente',
            'Servicio',
            'Monto (Bs)',
            'Método de Pago',
            'Tipo',
            'Estado',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Reporte de Ingresos';
    }
}

