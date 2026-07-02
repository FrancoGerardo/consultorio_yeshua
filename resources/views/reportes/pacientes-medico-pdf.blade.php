<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pacientes por Médico</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3498DB;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #3498DB;
            margin: 0;
        }
        .info {
            margin-bottom: 20px;
        }
        .estadisticas {
            background-color: #D6EAF8;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .estadisticas h3 {
            color: #3498DB;
            margin-top: 0;
        }
        .medico-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .medico-header {
            background-color: #3498DB;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .medico-stats {
            background-color: #EBF5FB;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #BDC3C7;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #5DADE2;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #F8F9FA;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #7F8C8D;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONSULTORIO MEDICO YESHUA</h1>
        <h2>Reporte de Pacientes por Médico</h2>
        <p>Generado el: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <strong>Filtros aplicados:</strong><br>
        @if(isset($filtros['fecha_inicio']))
            Desde: {{ $filtros['fecha_inicio'] }}<br>
        @endif
        @if(isset($filtros['fecha_fin']))
            Hasta: {{ $filtros['fecha_fin'] }}<br>
        @endif
    </div>

    <div class="estadisticas">
        <h3>Estadísticas Generales</h3>
        <p><strong>Total de Médicos:</strong> {{ $estadisticas['total_medicos'] }}</p>
        <p><strong>Total de Consultas:</strong> {{ $estadisticas['total_consultas'] }}</p>
        <p><strong>Promedio por Médico:</strong> {{ number_format($estadisticas['promedio_por_medico'], 2) }} consultas</p>
    </div>

    @foreach($medicos as $medicoData)
        <div class="medico-section">
            <div class="medico-header">
                <strong>Dr(a). {{ $medicoData['medico']->usuario->persona->nombre_completo }}</strong>
                @if($medicoData['medico']->especialidades->count() > 0)
                    <br>
                    <small>Especialidad: {{ $medicoData['medico']->especialidades->pluck('nombre')->implode(', ') }}</small>
                @endif
            </div>

            <div class="medico-stats">
                <strong>Total de Consultas:</strong> {{ $medicoData['total_consultas'] }} | 
                <strong>Pacientes Únicos:</strong> {{ $medicoData['pacientes_unicos'] }}
            </div>

            @if($medicoData['fichas']->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>Servicio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($medicoData['fichas'] as $ficha)
                        <tr>
                            <td>{{ $ficha->fecha->format('d/m/Y') }}</td>
                            <td>{{ $ficha->cliente->usuario->persona->nombre_completo ?? 'N/A' }}</td>
                            <td>{{ $ficha->servicio->nombre ?? 'N/A' }}</td>
                            <td>{{ $ficha->estado }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p><em>Sin consultas en el período seleccionado</em></p>
            @endif
        </div>
    @endforeach

    <div class="footer">
        <p>Consultorio Medico Yeshua - Sistema de Gestión Médica</p>
        <p>Página 1 de 1</p>
    </div>
</body>
</html>

