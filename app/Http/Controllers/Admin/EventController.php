<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        do {
            $defaultCode = 'CAP-' . date('Y') . '-' . strtoupper(Str::random(4));
        } while (Event::where('access_code', $defaultCode)->exists());

        return view('admin.events.create', compact('defaultCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'access_code' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'instructor' => 'nullable|string|max:255',
            'instructors' => 'nullable|array',
            'instructors.*' => 'nullable|string|max:150',
            'location' => 'nullable|string|max:150',
            'event_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'status' => 'required|in:active,completed,cancelled',
            'allow_registration' => 'boolean',
            'require_document' => 'boolean',
            'department_mode' => 'required|in:hidden,optional,required',
            'theme_color' => 'nullable|string|max:20',
            // Recurrencia / Multisesión
            'is_recurring' => 'nullable|boolean',
            'sessions' => 'nullable|array',
            'sessions.*.event_date' => 'required_with:is_recurring|date',
            'sessions.*.start_time' => 'nullable',
            'sessions.*.end_time' => 'nullable',
            'sessions.*.location' => 'nullable|string|max:150',
            'sessions.*.description' => 'nullable|string',
            'sessions.*.instructor' => 'nullable|string|max:255',
            'sessions.*.instructors' => 'nullable|array',
        ]);

        // Procesar instructores / expositores múltiples
        $instructorsList = [];
        if (!empty($validated['instructors']) && is_array($validated['instructors'])) {
            $instructorsList = array_values(array_filter(array_map('trim', $validated['instructors'])));
        } elseif (!empty($validated['instructor'])) {
            $instructorsList = array_values(array_filter(array_map('trim', explode(',', $validated['instructor']))));
        }

        $formattedInstructor = count($instructorsList) > 0 ? implode(', ', $instructorsList) : ($validated['instructor'] ?? null);

        // Asignar código único generado o validar que no exista colisión
        $code = !empty($request->access_code) ? strtoupper(trim($request->access_code)) : null;
        if (!$code || Event::where('access_code', $code)->exists()) {
            do {
                $code = 'CAP-' . date('Y') . '-' . strtoupper(Str::random(4));
            } while (Event::where('access_code', $code)->exists());
        }

        $eventData = [
            'title' => $validated['title'],
            'access_code' => $code,
            'description' => $validated['description'] ?? null,
            'instructor' => $formattedInstructor,
            'instructors' => $instructorsList,
            'location' => $validated['location'] ?? null,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'status' => $validated['status'],
            'allow_registration' => $request->boolean('allow_registration'),
            'override_closing' => false,
            'require_document' => $request->boolean('require_document'),
            'department_mode' => $validated['department_mode'],
            'theme_color' => $validated['theme_color'] ?? '#2563eb',
            'created_by' => Auth::id(),
            'session_number' => 1,
            'parent_id' => null,
        ];

        $rootEvent = Event::create($eventData);

        // Si se crearon sesiones recurrentes adicionales
        if ($request->boolean('is_recurring') && !empty($request->sessions) && is_array($request->sessions)) {
            $sessionNum = 2;
            foreach ($request->sessions as $sessionData) {
                if (empty($sessionData['event_date'])) {
                    continue;
                }

                // Generar código único para la sesión hija
                do {
                    $childCode = 'CAP-' . date('Y') . '-' . strtoupper(Str::random(4));
                } while (Event::where('access_code', $childCode)->exists());

                $childInstructors = [];
                if (!empty($sessionData['instructors']) && is_array($sessionData['instructors'])) {
                    $childInstructors = array_values(array_filter(array_map('trim', $sessionData['instructors'])));
                } elseif (!empty($sessionData['instructor'])) {
                    $childInstructors = array_values(array_filter(array_map('trim', explode(',', $sessionData['instructor']))));
                } else {
                    $childInstructors = $instructorsList; // Hereda de la principal si no se especifica
                }

                $childFormattedInstructor = count($childInstructors) > 0 ? implode(', ', $childInstructors) : $formattedInstructor;

                Event::create([
                    'parent_id' => $rootEvent->id,
                    'session_number' => $sessionNum,
                    'access_code' => $childCode,
                    'title' => $rootEvent->title,
                    'description' => !empty($sessionData['description']) ? $sessionData['description'] : $rootEvent->description,
                    'instructor' => $childFormattedInstructor,
                    'instructors' => $childInstructors,
                    'location' => !empty($sessionData['location']) ? $sessionData['location'] : $rootEvent->location,
                    'event_date' => $sessionData['event_date'],
                    'start_time' => !empty($sessionData['start_time']) ? $sessionData['start_time'] : $rootEvent->start_time,
                    'end_time' => !empty($sessionData['end_time']) ? $sessionData['end_time'] : $rootEvent->end_time,
                    'status' => $rootEvent->status,
                    'allow_registration' => $rootEvent->allow_registration,
                    'override_closing' => false,
                    'require_document' => $rootEvent->require_document,
                    'department_mode' => $rootEvent->department_mode,
                    'theme_color' => $rootEvent->theme_color,
                    'created_by' => Auth::id(),
                ]);

                $sessionNum++;
            }
        }

        $successMsg = 'Evento creado exitosamente con el código ' . $code . '.';
        if ($rootEvent->isRecurring()) {
            $total = $rootEvent->totalSeriesSessions();
            $successMsg .= " Se configuraron {$total} sesiones para esta serie/taller.";
        }

        return redirect()->route('admin.events.show', $rootEvent)->with('success', $successMsg);
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

        // Datos de serie y retención
        $seriesEvents = $event->isRecurring() ? $event->getAllSeriesEvents() : collect([$event]);
        $retention = $event->getRetentionMetrics();
        $missingAttendees = $event->getMissingAttendeesFromBase();

        return view('admin.events.show', compact('event', 'attendances', 'totalAttendees', 'seriesEvents', 'retention', 'missingAttendees'));
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructor' => 'nullable|string|max:255',
            'instructors' => 'nullable|array',
            'instructors.*' => 'nullable|string|max:150',
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

        $instructorsList = [];
        if (!empty($validated['instructors']) && is_array($validated['instructors'])) {
            $instructorsList = array_values(array_filter(array_map('trim', $validated['instructors'])));
        } elseif (!empty($validated['instructor'])) {
            $instructorsList = array_values(array_filter(array_map('trim', explode(',', $validated['instructor']))));
        }

        $formattedInstructor = count($instructorsList) > 0 ? implode(', ', $instructorsList) : ($validated['instructor'] ?? null);

        $validated['instructor'] = $formattedInstructor;
        $validated['instructors'] = $instructorsList;
        $validated['allow_registration'] = $request->boolean('allow_registration');
        $validated['require_document'] = $request->boolean('require_document');

        $event->update($validated);

        // Si es el evento principal y se añadieron nuevas sesiones desde la edición
        if ($event->parent_id === null && $request->boolean('is_recurring') && !empty($request->new_sessions) && is_array($request->new_sessions)) {
            $maxSessionNum = $event->sessions()->max('session_number') ?? 1;
            $nextNum = $maxSessionNum + 1;

            foreach ($request->new_sessions as $sessionData) {
                if (empty($sessionData['event_date'])) {
                    continue;
                }

                do {
                    $childCode = 'CAP-' . date('Y') . '-' . strtoupper(Str::random(4));
                } while (Event::where('access_code', $childCode)->exists());

                $childInstructors = [];
                if (!empty($sessionData['instructors']) && is_array($sessionData['instructors'])) {
                    $childInstructors = array_values(array_filter(array_map('trim', $sessionData['instructors'])));
                } elseif (!empty($sessionData['instructor'])) {
                    $childInstructors = array_values(array_filter(array_map('trim', explode(',', $sessionData['instructor']))));
                } else {
                    $childInstructors = $instructorsList;
                }

                $childFormattedInstructor = count($childInstructors) > 0 ? implode(', ', $childInstructors) : $formattedInstructor;

                Event::create([
                    'parent_id' => $event->id,
                    'session_number' => $nextNum,
                    'access_code' => $childCode,
                    'title' => $event->title,
                    'description' => !empty($sessionData['description']) ? $sessionData['description'] : $event->description,
                    'instructor' => $childFormattedInstructor,
                    'instructors' => $childInstructors,
                    'location' => !empty($sessionData['location']) ? $sessionData['location'] : $event->location,
                    'event_date' => $sessionData['event_date'],
                    'start_time' => !empty($sessionData['start_time']) ? $sessionData['start_time'] : $event->start_time,
                    'end_time' => !empty($sessionData['end_time']) ? $sessionData['end_time'] : $event->end_time,
                    'status' => $event->status,
                    'allow_registration' => true,
                    'override_closing' => false,
                    'require_document' => $event->require_document,
                    'department_mode' => $event->department_mode,
                    'theme_color' => $event->theme_color,
                    'created_by' => Auth::id(),
                ]);

                $nextNum++;
            }
        }

        return redirect()->route('admin.events.show', $event)->with('success', 'Evento actualizado correctamente.');
    }

    /**
     * Agregar una nueva sesión a un evento existente (convirtiéndolo en serie si era único).
     */
    public function storeSession(Request $request, Event $event)
    {
        $root = $event->getRootEvent();

        $validated = $request->validate([
            'event_date' => 'required|date|after_or_equal:' . $root->event_date->format('Y-m-d'),
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'nullable|string|max:150',
            'instructor' => 'nullable|string|max:255',
            'instructors' => 'nullable|array',
            'instructors.*' => 'nullable|string|max:150',
            'description' => 'nullable|string',
        ], [
            'event_date.after_or_equal' => 'La fecha de la nueva sesión no puede ser anterior a la fecha de inicio del evento (' . $root->event_date->format('d/m/Y') . ').',
        ]);

        $nextSessionNum = ($root->sessions()->max('session_number') ?? 1) + 1;

        do {
            $childCode = 'CAP-' . date('Y') . '-' . strtoupper(Str::random(4));
        } while (Event::where('access_code', $childCode)->exists());

        $instructorsList = [];
        if (!empty($validated['instructors']) && is_array($validated['instructors'])) {
            $instructorsList = array_values(array_filter(array_map('trim', $validated['instructors'])));
        } elseif (!empty($validated['instructor'])) {
            $instructorsList = array_values(array_filter(array_map('trim', explode(',', $validated['instructor']))));
        } else {
            $instructorsList = $root->instructors_list;
        }

        $formattedInstructor = count($instructorsList) > 0 ? implode(', ', $instructorsList) : $root->instructor;

        $newSession = Event::create([
            'parent_id' => $root->id,
            'session_number' => $nextSessionNum,
            'access_code' => $childCode,
            'title' => $root->title,
            'description' => !empty($validated['description']) ? $validated['description'] : $root->description,
            'instructor' => $formattedInstructor,
            'instructors' => $instructorsList,
            'location' => !empty($validated['location']) ? $validated['location'] : $root->location,
            'event_date' => $validated['event_date'],
            'start_time' => !empty($validated['start_time']) ? $validated['start_time'] : $root->start_time,
            'end_time' => !empty($validated['end_time']) ? $validated['end_time'] : $root->end_time,
            'status' => $root->status,
            'allow_registration' => true,
            'override_closing' => false,
            'require_document' => $root->require_document,
            'department_mode' => $root->department_mode,
            'theme_color' => $root->theme_color,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.events.show', $newSession)->with('success', "Se ha agregado la Sesión #{$nextSessionNum} exitosamente a la serie con código {$childCode}.");
    }

    public function toggleRegistration(Event $event)
    {
        // Si actualmente está abierto, lo cerramos
        if ($event->is_registration_open) {
            $event->update([
                'allow_registration' => false,
                'override_closing' => false,
            ]);
            $msg = 'El registro de asistencia ha sido cerrado/pausado manualmente.';
        } else {
            // Si estaba cerrado (ya sea por toggle previo o por haber pasado la hora de fin), lo reabrimos forzando override_closing
            $event->update([
                'allow_registration' => true,
                'override_closing' => true,
            ]);
            $msg = 'El registro de asistencia ha sido reabierto por el administrador.';
        }

        return back()->with('success', $msg);
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
            'allow_registration' => $event->is_registration_open,
            'registration_status_info' => $event->registration_status_info,
            'attendances' => $attendances,
        ]);
    }

    /**
     * Redirección a la pantalla pública de proyección de QR.
     */
    public function qr(Event $event)
    {
        return redirect()->route('attendance.qr', ['code' => $event->access_code]);
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

        $fileName = 'Asistencia_' . Str::slug($event->title) . ($event->isRecurring() ? '_S' . $event->session_number : '') . '_' . date('Ymd_His') . '.pdf';

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
        $sheet->setCellValue('B3', $event->title . ($event->isRecurring() ? " ({$event->series_session_label})" : ''));
        $sheet->getStyle('A3')->getFont()->setBold(true);

        $sheet->setCellValue('A4', 'Facilitador(es):');
        $sheet->setCellValue('B4', $event->formatted_instructors);
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
        $fileName = 'Asistencia_' . Str::slug($event->title) . ($event->isRecurring() ? '_S' . $event->session_number : '') . '_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Exportación de Matriz Consolidada de Asistencia de toda la serie en Excel (.xlsx).
     */
    public function exportSeriesExcel(Event $event)
    {
        $series = $event->getAllSeriesEvents();
        $root = $event->getRootEvent();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Matriz de Serie');

        // Título del reporte
        $sheet->setCellValue('A1', 'MATRIZ CONSOLIDADA DE ASISTENCIA Y RETENCIÓN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');

        $sheet->setCellValue('A3', 'Taller / Capacitación: ' . $root->title);
        $sheet->setCellValue('A4', 'Total de Sesiones: ' . $series->count());

        // Obtener todos los participantes que asistieron a al menos una sesión de la serie
        $allParticipantIds = Attendance::whereIn('event_id', $series->pluck('id'))
            ->pluck('participant_id')
            ->unique();

        $participants = \App\Models\Participant::whereIn('id', $allParticipantIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Encabezados
        $row = 6;
        $sheet->setCellValue('A' . $row, '#');
        $sheet->setCellValue('B' . $row, 'Código Empleado');
        $sheet->setCellValue('C' . $row, 'Cédula');
        $sheet->setCellValue('D' . $row, 'Participante');
        $sheet->setCellValue('E' . $row, 'Departamento / Área');

        $colIdx = 6; // Columna F
        foreach ($series as $s) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $sheet->setCellValue($colLetter . $row, "Sesión {$s->session_number} (" . $s->event_date->format('d/m') . ")");
            $sheet->getStyle($colLetter . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colIdx++;
        }

        $totalColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
        $sheet->setCellValue($totalColLetter . $row, 'Total Asistencias');
        $sheet->getStyle($totalColLetter . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A' . $row . ':' . $totalColLetter . $row)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A' . $row . ':' . $totalColLetter . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');

        // Mapear asistencias por sesión
        $attendancesMap = [];
        foreach ($series as $s) {
            $attendancesMap[$s->id] = Attendance::where('event_id', $s->id)->pluck('participant_id')->flip()->toArray();
        }

        $row = 7;
        foreach ($participants as $pIdx => $p) {
            $sheet->setCellValue('A' . $row, $pIdx + 1);
            $sheet->setCellValue('B' . $row, $p->employee_code ?? '');
            $sheet->setCellValue('C' . $row, $p->document_number ?? '');
            $sheet->setCellValue('D' . $row, $p->full_name);
            $sheet->setCellValue('E' . $row, $p->institution_department ?? '');

            $attendedCount = 0;
            $colIdx = 6;
            foreach ($series as $s) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $attended = isset($attendancesMap[$s->id][$p->id]);
                if ($attended) {
                    $attendedCount++;
                    $sheet->setCellValue($colLetter . $row, '✓ PRESENTE');
                    $sheet->getStyle($colLetter . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('166534'));
                    $sheet->getStyle($colLetter . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
                } else {
                    $sheet->setCellValue($colLetter . $row, '✗ AUSENTE');
                    $sheet->getStyle($colLetter . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('991B1B'));
                    $sheet->getStyle($colLetter . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');
                }
                $sheet->getStyle($colLetter . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $colIdx++;
            }

            $sheet->setCellValue($totalColLetter . $row, "{$attendedCount} / " . $series->count());
            $sheet->getStyle($totalColLetter . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($totalColLetter . $row)->getFont()->setBold(true);

            $row++;
        }

        // Autoajustar columnas
        for ($i = 1; $i <= $colIdx; $i++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Matriz_Serie_' . Str::slug($root->title) . '_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function destroyAttendance(Event $event, Attendance $attendance)
    {
        if ($attendance->event_id !== $event->id) {
            abort(404);
        }

        if ($attendance->signature_path && Storage::disk('public')->exists($attendance->signature_path)) {
            Storage::disk('public')->delete($attendance->signature_path);
        }

        $participantName = $attendance->participant ? $attendance->participant->full_name : 'Participante';
        $attendance->delete();

        return redirect()->route('admin.events.show', $event)
            ->with('success', "Se ha eliminado a {$participantName} de la lista de asistencia del evento.");
    }
}
