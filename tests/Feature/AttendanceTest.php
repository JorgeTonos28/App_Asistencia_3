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
}
