<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia - {{ $event->title }}</title>
    <style>
        @page {
            margin: 1.2cm 1.2cm 1.2cm 1.2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.3;
        }
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 3px 0;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 10px;
            color: #64748b;
            margin: 0;
        }
        .event-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 14px;
        }
        .event-card table {
            width: 100%;
            border-collapse: collapse;
        }
        .event-card td {
            padding: 2px 0;
            font-size: 10px;
        }
        .label {
            font-weight: bold;
            color: #334155;
            width: 100px;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .table-data th {
            background-color: #2563eb;
            color: #ffffff;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            padding: 6px 4px;
            border: 1px solid #1d4ed8;
            text-align: left;
        }
        .table-data td {
            padding: 5px 4px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            vertical-align: middle;
        }
        .table-data tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .signature-img {
            max-height: 28px;
            max-width: 80px;
            display: block;
            margin: 0 auto;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            text-align: center;
        }
        .badge {
            background-color: #dbeafe;
            color: #1e40af;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="vertical-align: middle;">
                    <h1 class="title">Lista Oficial de Asistencia</h1>
                    <p class="subtitle">Sistema de Control y Registro Digital de Eventos</p>
                </td>
                <td style="text-align: right; vertical-align: middle;">
                    <span class="badge">CÓDIGO: {{ $event->access_code }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="event-card">
        <table>
            <tr>
                <td class="label">Evento / Curso:</td>
                <td style="font-weight: bold; font-size: 11px; color: #0f172a;" colspan="3">{{ $event->title }}</td>
            </tr>
            <tr>
                <td class="label">Facilitador:</td>
                <td>{{ $event->instructor ?? 'No especificado' }}</td>
                <td class="label">Fecha:</td>
                <td>{{ $event->event_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Ubicación:</td>
                <td>{{ $event->location ?? 'No especificada' }}</td>
                <td class="label">Total Asistentes:</td>
                <td style="font-weight: bold; color: #2563eb;">{{ $attendances->count() }} participante(s)</td>
            </tr>
        </table>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 20px;" class="text-center">#</th>
                <th style="width: 55px;" class="text-center">Código</th>
                <th style="width: 80px;">Cédula</th>
                <th>Nombres y Apellidos</th>
                <th style="width: 75px;">Teléfono/Ext.</th>
                <th style="width: 85px;" class="text-center">Firma Digital</th>
                <th style="width: 50px;" class="text-center">Hora</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
                <tr>
                    <td class="text-center" style="font-weight: bold; color: #64748b;">{{ $index + 1 }}</td>
                    <td class="text-center" style="font-family: monospace; font-weight: bold;">{{ $attendance->participant->employee_code ?? '-' }}</td>
                    <td style="font-family: monospace;">{{ $attendance->participant->document_number ?? '-' }}</td>
                    <td>
                        <strong>{{ $attendance->participant->full_name }}</strong>
                    </td>
                    <td>{{ $attendance->participant->phone ?? '-' }}</td>
                    <td class="text-center">
                        @if($attendance->signature_path && file_exists(storage_path('app/public/' . $attendance->signature_path)))
                            <img src="{{ storage_path('app/public/' . $attendance->signature_path) }}" class="signature-img" alt="Firma">
                        @elseif($attendance->signature_base64)
                            <img src="{{ $attendance->signature_base64 }}" class="signature-img" alt="Firma">
                        @else
                            <span style="color: #94a3b8; font-style: italic; font-size: 8px;">Sin firma</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $attendance->check_in_at->format('h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #94a3b8;">
                        No se registraron asistencias en este evento.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dirección de Innovación y Análisis Estratégico de Datos - INNOVATEP · Generado el {{ date('d/m/Y \a \l\a\s h:i A') }}
    </div>
</body>
</html>
