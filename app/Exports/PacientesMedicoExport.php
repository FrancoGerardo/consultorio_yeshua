<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PacientesMedicoExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $medicos;
    protected $estadisticas;

    public function __construct($medicos, $estadisticas)
    {
        $this->medicos = $medicos;
        $this->estadisticas = $estadisticas;
    }

    public function collection()
    {
        $datos = collect();

        foreach ($this->medicos as $medicoData) {
            $medico = $medicoData['medico'];
            
            $datos->push([
                'Médico' => $medico->usuario->persona->nombre_completo,
                'Especialidad' => $medico->especialidades->pluck('nombre')->implode(', ') ?: 'N/A',
                'Total Consultas' => $medicoData['total_consultas'],
                'Pacientes Únicos' => $medicoData['pacientes_unicos'],
                'Promedio por Día' => $medicoData['total_consultas'] > 0 
                    ? round($medicoData['total_consultas'] / 30, 2) 
                    : 0,
            ]);

            // Agregar detalle de cada consulta
            foreach ($medicoData['fichas'] as $ficha) {
                $datos->push([
                    'Médico' => '  → ' . ($ficha->cliente->usuario->persona->nombre_completo ?? 'N/A'),
                    'Especialidad' => $ficha->fecha->format('d/m/Y'),
                    'Total Consultas' => $ficha->servicio->nombre ?? 'N/A',
                    'Pacientes Únicos' => $ficha->estado,
                    'Promedio por Día' => '',
                ]);
            }

            // Línea en blanco entre médicos
            $datos->push(['', '', '', '', '']);
        }

        return $datos;
    }

    public function headings(): array
    {
        return [
            'Médico',
            'Especialidad / Fecha',
            'Total Consultas / Servicio',
            'Pacientes Únicos / Estado',
            'Promedio por Día',
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
        return 'Pacientes por Médico';
    }
}

