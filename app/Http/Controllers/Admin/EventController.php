<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::withCount('attendances')->latest('event_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('instructor', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('access_code', 'like', "%{$search}%");
            });
        }

        $events = $query->paginate(12)->withQueryString();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        $defaultCode = strtoupper(Str::random(6));
        return view('admin.events.create', compact('defaultCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'access_code' => 'required|string|max:20|unique:events,access_code',
            'description' => 'nullable|string',
            'instructor' => 'nullable|string|max:150',
            'location' => 'nullable|string|max:150',
            'event_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'status' => 'required|in:active,completed,cancelled',
            'allow_registration' => 'boolean',
            'require_document' => 'boolean',
            'department_mode' => 'required|in:hidden,optional,required',
            'theme_color' => 'nullable|string|max:20',
        ]);

        $validated['access_code'] = strtoupper(trim($validated['access_code']));
        $validated['allow_registration'] = $request->boolean('allow_registration');
        $validated['require_document'] = $request->boolean('require_document');
        $validated['created_by'] = Auth::id();

        $event = Event::create($validated);

        return redirect()->route('admin.events.show', $event)->with('success', 'Evento creado exitosamente. Ya puedes compartir el enlace o código QR.');
    }

    public function show(Event $event, Request $request)
    {
        $attendancesQuery = $event->attendances()->with('participant');

        if ($request->filled('search')) {
            $search = $request->search;
            $attendancesQuery->whereHas('participant', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('institution_department', 'like', "%{$search}%");
            });
        }

        $attendances = $attendancesQuery->paginate(20)->withQueryString();
        $totalAttendees = $event->attendances()->count();

        return view('admin.events.show', compact('event', 'attendances', 'totalAttendees'));
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'access_code' => 'required|string|max:20|unique:events,access_code,' . $event->id,
            'description' => 'nullable|string',
            'instructor' => 'nullable|string|max:150',
            'location' => 'nullable|string|max:150',
            'event_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'status' => 'required|in:active,completed,cancelled',
            'allow_registration' => 'boolean',
            'require_document' => 'boolean',
            'department_mode' => 'required|in:hidden,optional,required',
            'theme_color' => 'nullable|string|max:20',
        ]);

        $validated['access_code'] = strtoupper(trim($validated['access_code']));
        $validated['allow_registration'] = $request->boolean('allow_registration');
        $validated['require_document'] = $request->boolean('require_document');

        $event->update($validated);

        return redirect()->route('admin.events.show', $event)->with('success', 'Evento actualizado correctamente.');
    }

    public function toggleRegistration(Event $event)
    {
        $event->update([
            'allow_registration' => !$event->allow_registration
        ]);

        return back()->with('success', $event->allow_registration ? 'Registro de asistencia habilitado.' : 'Registro de asistencia deshabilitado.');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Evento eliminado satisfactoriamente.');
    }

    /**
     * Vista de monitoreo en tiempo real (Live Attendance Monitor).
     */
    public function live(Event $event)
    {
        return view('admin.events.live', compact('event'));
    }

    /**
     * Endpoint API para alimentar la vista en tiempo real (Polling).
     */
    public function liveFeed(Event $event)
    {
        $attendances = $event->attendances()
            ->with('participant')
            ->latest('check_in_at')
            ->take(50)
            ->get()
            ->map(function ($att) {
                return [
                    'id' => $att->id,
                    'employee_code' => $att->participant->employee_code ?? 'N/A',
                    'document_number' => $att->participant->document_number ?? 'N/A',
                    'full_name' => $att->participant->full_name,
                    'phone' => $att->participant->phone,
                    'department' => $att->participant->institution_department ?? 'N/A',
                    'check_in_time' => $att->check_in_at->format('h:i:s A'),
                    'check_in_diff' => $att->check_in_at->diffForHumans(),
                    'signature_url' => $att->signature_url,
                ];
            });

        return response()->json([
            'count' => $event->attendances()->count(),
            'status' => $event->status,
            'allow_registration' => $event->allow_registration,
            'attendances' => $attendances,
        ]);
    }

    /**
     * Pantalla de Código QR y proyección.
     */
    public function qr(Event $event)
    {
        $registrationUrl = route('attendance.form', ['code' => $event->access_code]);

        return view('admin.events.qr', compact('event', 'registrationUrl'));
    }

    /**
     * Exportación de Hoja de Asistencia Oficial en Formato PDF.
     */
    public function exportPdf(Event $event)
    {
        $attendances = $event->attendances()->with('participant')->oldest('check_in_at')->get();

        $pdf = Pdf::loadView('admin.reports.pdf', compact('event', 'attendances'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        $fileName = 'Asistencia_' . Str::slug($event->title) . '_' . date('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Exportación de Reporte de Asistencia en Formato Excel (.xlsx).
     */
    public function exportExcel(Event $event)
    {
        $attendances = $event->attendances()->with('participant')->oldest('check_in_at')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Lista de Asistencia');

        // Estilos
        $headerColor = '1E3A8A'; // Azul corporativo oscuro

        // Título del reporte
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'REPORTE OFICIAL DE ASISTENCIA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($headerColor);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Metadatos del Evento
        $sheet->setCellValue('A3', 'Evento / Curso:');
        $sheet->setCellValue('B3', $event->title);
        $sheet->getStyle('A3')->getFont()->setBold(true);

        $sheet->setCellValue('A4', 'Facilitador:');
        $sheet->setCellValue('B4', $event->instructor ?? 'N/A');
        $sheet->getStyle('A4')->getFont()->setBold(true);

        $sheet->setCellValue('E3', 'Fecha:');
        $sheet->setCellValue('F3', $event->event_date->format('d/m/Y'));
        $sheet->getStyle('E3')->getFont()->setBold(true);

        $sheet->setCellValue('E4', 'Ubicación:');
        $sheet->setCellValue('F4', $event->location ?? 'N/A');
        $sheet->getStyle('E4')->getFont()->setBold(true);

        $sheet->setCellValue('G3', 'Total Asistentes:');
        $sheet->setCellValue('H3', $attendances->count());
        $sheet->getStyle('G3')->getFont()->setBold(true);
        $sheet->getStyle('H3')->getFont()->setBold(true);

        // Encabezados de la Tabla de Asistencia
        $row = 6;
        $headers = ['#', 'Código Empleado', 'Cédula', 'Nombres', 'Apellidos', 'Teléfono / Ext.', 'Departamento / Área', 'Hora Registro'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        foreach ($headers as $index => $header) {
            $col = $cols[$index];
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Llenar datos de participantes
        $row = 7;
        foreach ($attendances as $index => $attendance) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $attendance->participant->employee_code ?? '');
            $sheet->setCellValue('C' . $row, $attendance->participant->document_number ?? '');
            $sheet->setCellValue('D' . $row, $attendance->participant->first_name);
            $sheet->setCellValue('E' . $row, $attendance->participant->last_name);
            $sheet->setCellValue('F' . $row, $attendance->participant->phone ?? '');
            $sheet->setCellValue('G' . $row, $attendance->participant->institution_department ?? '');
            $sheet->setCellValue('H' . $row, $attendance->check_in_at->format('h:i:s A'));

            // Alineación centrada para número y hora
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Borde suave
            $sheet->getStyle('A' . $row . ':H' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

            // Fila alterna con fondo sutil
            if ($index % 2 === 1) {
                $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $row++;
        }

        // Auto-ajustar ancho de columnas
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Salida a descarga
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Asistencia_' . Str::slug($event->title) . '_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
