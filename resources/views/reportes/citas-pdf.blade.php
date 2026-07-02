<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Citas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2C3E50;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #2C3E50;
            margin: 0;
        }
        .info {
            margin-bottom: 20px;
        }
        .estadisticas {
            background-color: #ECF0F1;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #BDC3C7;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #34495E;
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
        <h2>Reporte de Citas</h2>
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
        @if(isset($filtros['estado']))
            Estado: {{ $filtros['estado'] }}<br>
        @endif
    </div>

    <div class="estadisticas">
        <h3>Estadísticas Generales</h3>
        <p><strong>Total de Citas:</strong> {{ $estadisticas['total_citas'] }}</p>
        
        <strong>Por Estado:</strong><br>
        @foreach($estadisticas['por_estado'] as $estado => $cantidad)
            {{ $estado }}: {{ $cantidad }}<br>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Médico</th>
                <th>Servicio</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fichas as $ficha)
            <tr>
                <td>{{ $ficha->fecha->format('d/m/Y') }}</td>
                <td>{{ substr($ficha->hora, 0, 5) }}</td>
                <td>{{ $ficha->cliente->usuario->persona->nombre_completo ?? 'N/A' }}</td>
                <td>{{ $ficha->medico->usuario->persona->nombre_completo ?? 'N/A' }}</td>
                <td>{{ $ficha->servicio->nombre ?? 'N/A' }}</td>
                <td>{{ $ficha->estado }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Consultorio Medico Yeshua - Sistema de Gestión Médica</p>
        <p>Página 1 de 1</p>
    </div>
</body>
</html>

