<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Generado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2C3E50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #3498DB;
        }
        .button {
            display: inline-block;
            background-color: #3498DB;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏥 Consultorio Medico Yeshua</h1>
        <p>Sistema de Gestión Médica</p>
    </div>

    <div class="content">
        <h2>Su reporte ha sido generado exitosamente</h2>
        
        <p>Estimado usuario,</p>
        
        <p>Le informamos que su reporte ha sido procesado y está disponible para su descarga.</p>

        <div class="info-box">
            <strong>📊 Detalles del Reporte:</strong><br><br>
            <strong>Nombre:</strong> {{ $reporte->nombre }}<br>
            <strong>Tipo:</strong> {{ ucfirst(str_replace('_', ' ', $reporte->tipo)) }}<br>
            <strong>Formato:</strong> {{ strtoupper($reporte->formato) }}<br>
            <strong>Fecha de generación:</strong> {{ $reporte->fecha_generacion->format('d/m/Y H:i') }}<br>
        </div>

        @if(isset($reporte->filtros['fecha_inicio']) || isset($reporte->filtros['fecha_fin']))
        <div class="info-box">
            <strong>📅 Filtros Aplicados:</strong><br><br>
            @if(isset($reporte->filtros['fecha_inicio']))
                <strong>Desde:</strong> {{ $reporte->filtros['fecha_inicio'] }}<br>
            @endif
            @if(isset($reporte->filtros['fecha_fin']))
                <strong>Hasta:</strong> {{ $reporte->filtros['fecha_fin'] }}<br>
            @endif
        </div>
        @endif

        <p>
            <strong>El reporte se encuentra adjunto a este correo electrónico.</strong>
        </p>

        <p>
            También puede descargarlo directamente desde el sistema accediendo a la sección de reportes generados.
        </p>

        <p>Si tiene alguna pregunta o necesita asistencia, no dude en contactarnos.</p>

        <p>Atentamente,<br>
        <strong>Equipo de Consultorio Medico Yeshua</strong></p>
    </div>

    <div class="footer">
        <p>Este es un correo automático, por favor no responder.</p>
        <p>&copy; {{ date('Y') }} Consultorio Medico Yeshua. Todos los derechos reservados.</p>
    </div>
</body>
</html>

