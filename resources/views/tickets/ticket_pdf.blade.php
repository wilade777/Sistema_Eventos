<!DOCTYPE html>
<html>
<head>
    <title>Ticket de Evento - {{ $ticket->evento->nombre }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #4f46e5; /* indigo-600 */
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
            text-align: center;
        }
        .content h2 {
            color: #4f46e5;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .details {
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.6;
        }
        .details p {
            margin: 5px 0;
        }
        .qr-code {
            margin: 30px auto;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 5px;
            width: 220px; /* Ancho para el QR */
            height: 220px; /* Alto para el QR */
            display: flex;
            justify-content: center;
            align-items: center;
        }
        /* --- CAMBIO AQUÍ: Estilos para la imagen QR --- */
        .qr-code img {
            width: 200px;
            height: 200px;
            display: block; /* Asegura que la imagen ocupe su propio espacio */
            margin: 0 auto; /* Centra la imagen dentro de su contenedor */
        }
        /* --- FIN DEL CAMBIO --- */
        .footer {
            background-color: #f0f0f0;
            color: #777;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ticket de Evento</h1>
        </div>
        <div class="content">
            <h2>{{ $ticket->evento->nombre }}</h2>
            <div class="details">
                <p><strong>Tipo de Ticket:</strong> {{ $ticket->tipo }}</p>
                <p><strong>Precio:</strong> ${{ number_format($ticket->precio, 2) }}</p>
                <p><strong>Asistente:</strong> {{ $ticket->asistente->nombre }}</p>
                <p><strong>Correo:</strong> {{ $ticket->asistente->correo }}</p>
                <p><strong>Fecha del Evento:</strong> {{ \Carbon\Carbon::parse($ticket->evento->fecha)->format('d/m/Y') }}</p>
                <p><strong>Hora del Evento:</strong> {{ \Carbon\Carbon::parse($ticket->evento->hora)->format('H:i') }}</p>
                <p><strong>Ubicación:</strong> {{ $ticket->evento->ubicacion }}</p>
                <p><strong>Estado:</strong> {{ $ticket->usado ? 'Usado' : 'Activo' }}</p>
            </div>
            <div class="qr-code">
                {{-- --- CAMBIO AQUÍ: Usar la imagen Base64 --- --}}
                <img src="data:image/png;base64,{{ $qrCodePngBase64 }}" alt="Código QR del Ticket">
                {{-- --- FIN DEL CAMBIO --- --}}
            </div>
            <p>Presenta este código QR al ingresar al evento.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} EventosVIP. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>