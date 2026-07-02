<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CitasExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $fichas;
    protected $estadisticas;

    public function __construct($fichas, $estadisticas)
    {
        $this->fichas = $fichas;
        $this->estadisticas = $estadisticas;
    }

    public function collection()
    {
        return $this->fichas->map(function($ficha) {
            return [
                'ID' => $ficha->id,
                'Fecha' => $ficha->fecha->format('d/m/Y'),
                'Hora' => substr($ficha->hora, 0, 5),
                'Paciente' => $ficha->cliente->usuario->persona->nombre_completo ?? 'N/A',
                'Médico' => $ficha->medico->usuario->persona->nombre_completo ?? 'N/A',
                'Servicio' => $ficha->servicio->nombre ?? 'N/A',
                'Sala' => $ficha->sala->numero ?? 'N/A',
                'Estado' => $ficha->estado,
                'Motivo' => $ficha->motivo_consulta ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Hora',
            'Paciente',
            'Médico',
            'Servicio',
            'Sala',
            'Estado',
            'Motivo de Consulta',
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
        return 'Reporte de Citas';
    }
}

