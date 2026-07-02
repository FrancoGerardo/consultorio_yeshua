<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ingresos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #27AE60;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #27AE60;
            margin: 0;
        }
        .info {
            margin-bottom: 20px;
        }
        .estadisticas {
            background-color: #D5F4E6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .estadisticas h3 {
            color: #27AE60;
            margin-top: 0;
        }
        .total-destacado {
            font-size: 18px;
            font-weight: bold;
            color: #27AE60;
            margin: 10px 0;
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
            background-color: #27AE60;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #F8F9FA;
        }
        .monto {
            text-align: right;
            font-weight: bold;
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
        <h2>Reporte de Ingresos</h2>
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
        @if(isset($filtros['metodo_pago']))
            Método de Pago: {{ $filtros['metodo_pago'] }}<br>
        @endif
    </div>

    <div class="estadisticas">
        <h3>Resumen Financiero</h3>
        <p class="total-destacado">
            TOTAL INGRESOS: Bs. {{ number_format($estadisticas['total_ingresos'], 2) }}
        </p>
        <p><strong>Total de Transacciones:</strong> {{ $estadisticas['total_transacciones'] }}</p>
        <p><strong>Promedio por Transacción:</strong> Bs. {{ number_format($estadisticas['promedio_transaccion'], 2) }}</p>
        
        <strong>Por Método de Pago:</strong><br>
        @foreach($estadisticas['por_metodo'] as $metodo => $datos)
            <strong>{{ $metodo }}:</strong> {{ $datos['cantidad'] }} transacciones = Bs. {{ number_format($datos['monto'], 2) }}<br>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Servicio</th>
                <th>Método</th>
                <th class="monto">Monto (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagos as $pago)
            <tr>
                <td>{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                <td>{{ $pago->ficha->cliente->usuario->persona->nombre_completo ?? 'N/A' }}</td>
                <td>{{ $pago->ficha->servicio->nombre ?? 'N/A' }}</td>
                <td>{{ $pago->metodo_pago }}</td>
                <td class="monto">{{ number_format($pago->monto, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align: right;">TOTAL:</th>
                <th class="monto">Bs. {{ number_format($estadisticas['total_ingresos'], 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Consultorio Medico Yeshua - Sistema de Gestión Médica</p>
        <p>Página 1 de 1</p>
    </div>
</body>
</html>

