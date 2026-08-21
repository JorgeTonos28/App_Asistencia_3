<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_participant_lookup_by_code_and_document()
    {
        $participant = Participant::create([
            'employee_code' => '5445',
            'document_number' => '402-2022923-7',
            'first_name' => 'Jorge Antonio',
            'last_name' => 'Tonos Carrasco',
            'phone' => '8299383558',
            'email' => 'jtonos@infotep.gob.do',
            'institution_department' => 'INFOTEP',
        ]);

        // Lookup by Code
        $responseCode = $this->getJson(route('participants.lookup', ['term' => '5445']));
        $responseCode->assertStatus(200)
            ->assertJson([
                'found' => true,
                'participant' => [
                    'employee_code' => '5445',
                    'document_number' => '402-2022923-7',
                    'first_name' => 'Jorge Antonio',
                ]
            ]);

        // Lookup by Cedula without hyphens
        $responseDoc = $this->getJson(route('participants.lookup', ['term' => '40220229237']));
        $responseDoc->assertStatus(200)
            ->assertJson([
                'found' => true,
                'participant' => [
                    'employee_code' => '5445',
                ]
            ]);
    }

    public function test_public_attendance_registration_with_signature()
    {
        $event = Event::create([
            'access_code' => 'TEST-01',
            'title' => 'Evento de Prueba',
            'event_date' => now()->toDateString(),
            'status' => 'active',
            'allow_registration' => true,
            'require_document' => false,
            'department_mode' => 'optional',
        ]);

        $fakeSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->post(route('attendance.register', ['code' => 'TEST-01']), [
            'employee_code' => '9999',
            'document_number' => '402-9999999-1',
            'first_name' => 'Maria',
            'last_name' => 'Mercedes',
            'phone' => '829-555-9999',
            'email' => 'maria@test.com',
            'institution_department' => 'Contabilidad',
            'signature' => $fakeSignature,
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('participants', [
            'employee_code' => '9999',
            'first_name' => 'Maria',
            'last_name' => 'Mercedes',
        ]);

        $this->assertDatabaseHas('attendances', [
            'event_id' => $event->id,
        ]);
    }

    public function test_public_qr_projection_screen_is_accessible()
    {
        $event = Event::create([
            'access_code' => 'PROJ-99',
            'title' => 'Evento Proyección',
            'event_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->get(route('attendance.qr', ['code' => 'PROJ-99']));
        $response->assertStatus(200);

        $responseFeed = $this->getJson(route('attendance.live_feed', ['code' => 'PROJ-99']));
        $responseFeed->assertStatus(200)
            ->assertJsonStructure(['count', 'status', 'allow_registration', 'attendances']);
    }

    public function test_admin_can_remove_person_from_event_attendance_list()
    {
        $admin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $event = Event::create([
            'access_code' => 'TEST-DEL-01',
            'title' => 'Evento a Limpiar Asistencia',
            'event_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $participant = Participant::create([
            'employee_code' => '8888',
            'document_number' => '402-8888888-8',
            'first_name' => 'Carlos',
            'last_name' => 'Santana',
        ]);

        $attendance = $event->attendances()->create([
            'participant_id' => $participant->id,
            'signature_path' => 'signatures/test_signature.png',
            'check_in_at' => now(),
        ]);

        Storage::disk('public')->put('signatures/test_signature.png', 'fake_image_content');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'event_id' => $event->id,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.events.attendances.destroy', [$event, $attendance]));

        $response->assertRedirect(route('admin.events.show', $event));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);

        // Verify participant is preserved in catalogue
        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
        ]);

        // Verify signature file was removed
        $this->assertFalse(Storage::disk('public')->exists('signatures/test_signature.png'));
    }

    public function test_guest_cannot_remove_person_from_event_attendance_list()
    {
        $event = Event::create([
            'access_code' => 'TEST-DEL-02',
            'title' => 'Evento Privado',
            'event_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $participant = Participant::create([
            'employee_code' => '7777',
            'first_name' => 'Luis',
            'last_name' => 'Gomez',
        ]);

        $attendance = $event->attendances()->create([
            'participant_id' => $participant->id,
            'check_in_at' => now(),
        ]);

        $response = $this->delete(route('admin.events.attendances.destroy', [$event, $attendance]));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
        ]);
    }
}
