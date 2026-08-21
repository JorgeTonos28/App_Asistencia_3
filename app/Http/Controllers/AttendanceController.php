<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    /**
     * Muestra el formulario público de registro de asistencia.
     */
    public function showForm(string $code)
    {
        $event = Event::where('access_code', $code)->firstOrFail();

        return view('attendance.form', compact('event'));
    }

    /**
     * Endpoint AJAX para buscar participante previo por Código de Empleado o Cédula.
     */
    public function lookupParticipant(Request $request)
    {
        $code = trim($request->query('code', ''));
        $document = trim($request->query('document', ''));
        $term = trim($request->query('term', $code ?: $document));

        if (empty($term)) {
            return response()->json(['found' => false]);
        }

        $cleanTerm = preg_replace('/\s+/', '', $term);
        $cleanNoHyphen = str_replace('-', '', $cleanTerm);

        // 1. Buscar primero por Código de Empleado exacto o limpio
        $participant = Participant::where('employee_code', $term)
            ->orWhere('employee_code', $cleanTerm)
            ->first();

        // 2. Si no se encuentra, buscar por Cédula (con o sin guiones/espacios)
        if (!$participant) {
            $participant = Participant::where('document_number', $term)
                ->orWhereRaw("REPLACE(document_number, ' ', '') = ?", [$cleanTerm])
                ->orWhereRaw("REPLACE(REPLACE(document_number, '-', ''), ' ', '') = ?", [$cleanNoHyphen])
                ->first();
        }

        if ($participant) {
            return response()->json([
                'found' => true,
                'participant' => [
                    'employee_code' => $participant->employee_code,
                    'document_number' => $participant->document_number,
                    'first_name' => $participant->first_name,
                    'last_name' => $participant->last_name,
                    'phone' => $participant->phone,
                    'email' => $participant->email,
                    'institution_department' => $participant->institution_department,
                ]
            ]);
        }

        return response()->json(['found' => false]);
    }

    /**
     * Procesa y guarda el registro de asistencia con firma.
     */
    public function register(Request $request, string $code)
    {
        $event = Event::where('access_code', $code)->firstOrFail();

        if (!$event->allow_registration || $event->status === 'cancelled') {
            return back()->with('error', 'El registro de asistencia para este evento se encuentra cerrado o no disponible.');
        }

        // Reglas dinámicas según la configuración del evento
        $rules = [
            'employee_code' => 'required|string|max:50',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50', // Ahora opcional
            'email' => 'nullable|email|max:150',
            'signature' => 'required|string', // Base64 Data URL
            'notes' => 'nullable|string|max:255',
        ];

        // Cédula obligatoria u opcional según configuración del evento
        if ($event->require_document) {
            $rules['document_number'] = 'required|string|max:50';
        } else {
            $rules['document_number'] = 'nullable|string|max:50';
        }

        // Departamento según modo configurado
        if ($event->department_mode === 'required') {
            $rules['institution_department'] = 'required|string|max:150';
        } else {
            $rules['institution_department'] = 'nullable|string|max:150';
        }

        $messages = [
            'employee_code.required' => 'El código de empleado es obligatorio.',
            'document_number.required' => 'La cédula es obligatoria para este evento.',
            'first_name.required' => 'Los nombres son obligatorios.',
            'last_name.required' => 'Los apellidos son obligatorios.',
            'institution_department.required' => 'El departamento / área es obligatorio para este evento.',
            'signature.required' => 'La firma digital es obligatoria.',
        ];

        $validated = $request->validate($rules, $messages);

        $empCode = trim($validated['employee_code']);
        $docNumber = !empty($validated['document_number']) ? trim($validated['document_number']) : null;

        // 1. Guardar o actualizar la información del participante en el catálogo global
        $participant = null;
        if (!empty($empCode)) {
            $participant = Participant::where('employee_code', $empCode)->first();
        }
        if (!$participant && !empty($docNumber)) {
            $participant = Participant::where('document_number', $docNumber)->first();
        }

        $participantData = [
            'employee_code' => $empCode,
            'document_number' => $docNumber ?? ($participant?->document_number),
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'phone' => !empty($validated['phone']) ? trim($validated['phone']) : ($participant?->phone),
            'email' => !empty($validated['email']) ? trim($validated['email']) : ($participant?->email),
            'institution_department' => !empty($validated['institution_department']) ? trim($validated['institution_department']) : ($participant?->institution_department),
        ];

        if ($participant) {
            $participant->update($participantData);
        } else {
            $participant = Participant::create($participantData);
        }

        // 2. Verificar si ya se registró en este evento
        $existing = Attendance::where('event_id', $event->id)
            ->where('participant_id', $participant->id)
            ->first();

        if ($existing) {
            return redirect()->route('attendance.confirmation', ['code' => $event->access_code, 'attendance' => $existing->id])
                ->with('warning', 'Ya habías registrado tu asistencia para este evento previamente.');
        }

        // 3. Procesar y almacenar la imagen de la firma PNG
        $signaturePath = null;
        if (!empty($validated['signature']) && str_starts_with($validated['signature'], 'data:image')) {
            $imageParts = explode(';base64,', $validated['signature']);
            if (count($imageParts) === 2) {
                $imageBase64 = base64_decode($imageParts[1]);
                $filename = 'signatures/' . $event->id . '_' . $participant->id . '_' . time() . '_' . Str::random(6) . '.png';
                Storage::disk('public')->put($filename, $imageBase64);
                $signaturePath = $filename;
            }
        }

        // 4. Crear registro de asistencia
        $attendance = Attendance::create([
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'signature_path' => $signaturePath,
            'check_in_at' => now(),
            'notes' => $validated['notes'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('attendance.confirmation', [
            'code' => $event->access_code,
            'attendance' => $attendance->id
        ])->with('success', '¡Tu asistencia ha sido registrada exitosamente!');
    }

    /**
     * Pantalla de confirmación y comprobante.
     */
    public function confirmation(string $code, Attendance $attendance)
    {
        $event = Event::where('access_code', $code)->firstOrFail();
        $attendance->load('participant');

        return view('attendance.confirmation', compact('event', 'attendance'));
    }

    /**
     * Pantalla Pública de Proyección de Código QR y Registros en Vivo.
     */
    public function publicQrProjection(string $code)
    {
        $event = Event::where('access_code', $code)->firstOrFail();
        $registrationUrl = route('attendance.form', ['code' => $event->access_code]);

        return view('admin.events.qr', compact('event', 'registrationUrl'));
    }

    /**
     * Endpoint API público para alimentar la proyección en vivo (Polling).
     */
    public function publicLiveFeed(string $code)
    {
        $event = Event::where('access_code', $code)->firstOrFail();

        $attendances = $event->attendances()
            ->with('participant')
            ->latest('check_in_at')
            ->take(30)
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
}
