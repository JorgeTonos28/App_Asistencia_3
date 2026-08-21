<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecurringEventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_admin_can_create_recurring_event_series_with_multiple_sessions_and_instructors()
    {
        $admin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $postData = [
            'title' => 'Taller de Liderazgo y Productividad',
            'description' => 'Taller intensivo de 3 días',
            'instructors' => ['Ing. Laura Morales', 'Lic. Carlos Santana'],
            'location' => 'Salón Multiusos A',
            'event_date' => '2026-09-10',
            'start_time' => '09:00',
            'end_time' => '13:00',
            'status' => 'active',
            'allow_registration' => 1,
            'department_mode' => 'optional',
            'is_recurring' => 1,
            'sessions' => [
                [
                    'event_date' => '2026-09-11',
                    'start_time' => '09:00',
                    'end_time' => '13:00',
                    'location' => 'Salón Multiusos B',
                    'instructor' => 'Ing. Laura Morales',
                    'description' => 'Día 2: Aplicación en campo',
                ],
                [
                    'event_date' => '2026-09-12',
                    'start_time' => '09:00',
                    'end_time' => '13:00',
                    'location' => 'Salón Multiusos A',
                    'instructor' => 'Lic. Carlos Santana',
                    'description' => 'Día 3: Cierre y evaluaciones',
                ]
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.events.store'), $postData);

        $response->assertRedirect();
        $this->assertDatabaseHas('events', [
            'title' => 'Taller de Liderazgo y Productividad',
            'session_number' => 1,
            'parent_id' => null,
        ]);

        $parentEvent = Event::where('title', 'Taller de Liderazgo y Productividad')->where('session_number', 1)->first();
        $this->assertNotNull($parentEvent);
        $this->assertEquals(2, $parentEvent->sessions()->count());

        $this->assertDatabaseHas('events', [
            'parent_id' => $parentEvent->id,
            'session_number' => 2,
            'event_date' => '2026-09-11 00:00:00',
        ]);

        $this->assertDatabaseHas('events', [
            'parent_id' => $parentEvent->id,
            'session_number' => 3,
            'event_date' => '2026-09-12 00:00:00',
        ]);

        // Verify multiple instructors formatting
        $this->assertStringContainsString('Laura Morales', $parentEvent->formatted_instructors);
        $this->assertStringContainsString('Carlos Santana', $parentEvent->formatted_instructors);
    }

    public function test_retention_and_missing_attendees_tracking_between_sessions()
    {
        $parentEvent = Event::create([
            'access_code' => 'TALLER-S1',
            'title' => 'Taller de Datos',
            'event_date' => '2026-09-10',
            'status' => 'active',
            'session_number' => 1,
            'allow_registration' => true,
        ]);

        $session2 = Event::create([
            'parent_id' => $parentEvent->id,
            'access_code' => 'TALLER-S2',
            'title' => 'Taller de Datos',
            'event_date' => '2026-09-11',
            'status' => 'active',
            'session_number' => 2,
            'allow_registration' => true,
        ]);

        // Crear 3 participantes
        $p1 = Participant::create(['employee_code' => '1001', 'first_name' => 'Juan', 'last_name' => 'Perez']);
        $p2 = Participant::create(['employee_code' => '1002', 'first_name' => 'Ana', 'last_name' => 'Gomez']);
        $p3 = Participant::create(['employee_code' => '1003', 'first_name' => 'Luis', 'last_name' => 'Martinez']);

        // Registrar a los 3 en Sesión 1
        $parentEvent->attendances()->create(['participant_id' => $p1->id, 'check_in_at' => now()]);
        $parentEvent->attendances()->create(['participant_id' => $p2->id, 'check_in_at' => now()]);
        $parentEvent->attendances()->create(['participant_id' => $p3->id, 'check_in_at' => now()]);

        // Registrar solo a p1 y p2 en Sesión 2 (p3 faltó a la Sesión 2)
        $session2->attendances()->create(['participant_id' => $p1->id, 'check_in_at' => now()]);
        $session2->attendances()->create(['participant_id' => $p2->id, 'check_in_at' => now()]);

        // Verificar métricas de retención de Sesión 2 vs Sesión 1
        $metrics = $session2->getRetentionMetrics();
        $this->assertFalse($metrics['is_base_session']);
        $this->assertEquals(3, $metrics['base_total']);
        $this->assertEquals(2, $metrics['current_total']);
        $this->assertEquals(2, $metrics['retained_count']);
        $this->assertEquals(1, $metrics['missing_count']);
        $this->assertEquals(66.7, $metrics['retention_rate']);

        // Verificar que getMissingAttendeesFromBase retorne exactamente a p3
        $missing = $session2->getMissingAttendeesFromBase();
        $this->assertCount(1, $missing);
        $this->assertEquals($p3->id, $missing->first()->id);
        $this->assertEquals('Luis Martinez', $missing->first()->full_name);
    }

    public function test_automatic_registration_closing_when_event_end_time_passes()
    {
        // Evento que finalizó en el pasado
        $pastEvent = Event::create([
            'access_code' => 'PAST-EVENT',
            'title' => 'Evento Pasado',
            'event_date' => Carbon::yesterday()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'status' => 'active',
            'allow_registration' => true,
            'override_closing' => false,
        ]);

        $this->assertTrue($pastEvent->is_past_end_time);
        $this->assertFalse($pastEvent->is_registration_open);
        $this->assertEquals('expired_schedule', $pastEvent->registration_status_info['reason']);

        // Intentar registrarse en evento con horario vencido debe ser rechazado
        $response = $this->post(route('attendance.register', ['code' => 'PAST-EVENT']), [
            'employee_code' => '5001',
            'first_name' => 'Pedro',
            'last_name' => 'Picapiedra',
            'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('attendances', [
            'event_id' => $pastEvent->id,
        ]);

        // Administrador reabre el registro manualmente (override_closing)
        $admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin)->post(route('admin.events.toggle_registration', $pastEvent));

        $pastEvent->refresh();
        $this->assertTrue($pastEvent->override_closing);
        $this->assertTrue($pastEvent->is_registration_open);

        // Ahora el participante puede registrarse satisfactoriamente
        $responseSuccess = $this->post(route('attendance.register', ['code' => 'PAST-EVENT']), [
            'employee_code' => '5001',
            'first_name' => 'Pedro',
            'last_name' => 'Picapiedra',
            'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $responseSuccess->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'event_id' => $pastEvent->id,
        ]);
    }

    public function test_admin_can_export_series_excel_matrix()
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        $parentEvent = Event::create([
            'access_code' => 'SERIES-01',
            'title' => 'Diplomado en Gestión de Proyectos',
            'event_date' => '2026-09-10',
            'status' => 'active',
            'session_number' => 1,
        ]);

        $session2 = Event::create([
            'parent_id' => $parentEvent->id,
            'access_code' => 'SERIES-02',
            'title' => 'Diplomado en Gestión de Proyectos',
            'event_date' => '2026-09-11',
            'status' => 'active',
            'session_number' => 2,
        ]);

        $participant = Participant::create([
            'employee_code' => '2001',
            'first_name' => 'Carla',
            'last_name' => 'Morillo',
        ]);

        $parentEvent->attendances()->create(['participant_id' => $participant->id, 'check_in_at' => now()]);
        $session2->attendances()->create(['participant_id' => $participant->id, 'check_in_at' => now()]);

        $response = $this->actingAs($admin)
            ->get(route('admin.events.export_series_excel', $parentEvent));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_convert_existing_single_event_to_recurring_by_adding_session()
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        // Evento existente creado como evento único
        $singleEvent = Event::create([
            'access_code' => 'UNICO-001',
            'title' => 'Taller de Excel Avanzado',
            'event_date' => '2026-08-25',
            'status' => 'active',
            'session_number' => 1,
            'parent_id' => null,
            'instructor' => 'Lic. Roberto Díaz',
        ]);

        // Registrar participantes en el evento existente
        $p1 = Participant::create(['employee_code' => '8001', 'first_name' => 'Mario', 'last_name' => 'Bros']);
        $singleEvent->attendances()->create(['participant_id' => $p1->id, 'check_in_at' => now()]);

        $this->assertFalse($singleEvent->isRecurring());
        $this->assertEquals(1, $singleEvent->attendances()->count());

        // El admin decide agregar el Día 2 a este evento
        $response = $this->actingAs($admin)
            ->post(route('admin.events.sessions.store', $singleEvent), [
                'event_date' => '2026-08-26',
                'start_time' => '09:00',
                'end_time' => '12:00',
                'location' => 'Laboratorio 2',
                'instructor' => 'Lic. Roberto Díaz, Ing. Mario Casas',
                'description' => 'Día 2: Macros y VBA',
            ]);

        $response->assertRedirect();

        $singleEvent->refresh();
        $this->assertTrue($singleEvent->isRecurring());
        $this->assertEquals(1, $singleEvent->sessions()->count());

        $session2 = $singleEvent->sessions()->first();
        $this->assertEquals(2, $session2->session_number);
        $this->assertEquals($singleEvent->id, $session2->parent_id);
        $this->assertEquals('2026-08-26 00:00:00', $session2->event_date->format('Y-m-d H:i:s'));
        $this->assertEquals('Laboratorio 2', $session2->location);

        // Las firmas de la Sesión 1 siguen intactas
        $this->assertEquals(1, $singleEvent->attendances()->count());
    }

    public function test_cannot_create_session_with_date_prior_to_base_event()
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        $baseEvent = Event::create([
            'access_code' => 'BASE-001',
            'title' => 'Conferencia Anual',
            'event_date' => '2026-08-25',
            'status' => 'active',
            'session_number' => 1,
            'parent_id' => null,
        ]);

        // Intentar agregar una sesión con fecha anterior a la del evento base
        $response = $this->actingAs($admin)
            ->post(route('admin.events.sessions.store', $baseEvent), [
                'event_date' => '2026-08-20', // Fecha en el pasado relativo
            ]);

        $response->assertSessionHasErrors('event_date');
        $this->assertEquals(0, $baseEvent->sessions()->count());
    }
}
