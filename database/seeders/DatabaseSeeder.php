<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear Usuario Administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@asistencia.com'],
            [
                'name' => 'Administrador General',
                'role' => 'superadmin',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Crear Eventos de Demostración
        $evento1 = Event::firstOrCreate(
            ['access_code' => 'CAP-2026'],
            [
                'title' => 'Taller de Seguridad y Salud en el Trabajo',
                'description' => 'Capacitación obligatoria trimestral para todo el personal operativo y administrativo.',
                'instructor' => 'Ing. Carlos Mendoza',
                'location' => 'Salón Multiusos A - Piso 2',
                'event_date' => now()->toDateString(),
                'start_time' => '09:00',
                'end_time' => '13:00',
                'status' => 'active',
                'allow_registration' => true,
                'theme_color' => '#2563eb',
                'created_by' => $admin->id,
            ]
        );

        $evento2 = Event::firstOrCreate(
            ['access_code' => 'PROG-101'],
            [
                'title' => 'Curso de Transformación Digital e IA Aplicada',
                'description' => 'Estrategias de adopción de herramientas tecnológicas en procesos corporativos.',
                'instructor' => 'Dra. María Elena Peña',
                'location' => 'Auditorio Principal',
                'event_date' => now()->addDays(2)->toDateString(),
                'start_time' => '14:00',
                'end_time' => '18:00',
                'status' => 'active',
                'allow_registration' => true,
                'theme_color' => '#7c3aed',
                'created_by' => $admin->id,
            ]
        );

        // 3. Crear Participantes de Ejemplo
        $p1 = Participant::firstOrCreate(
            ['document_number' => '402-1234567-8'],
            [
                'first_name' => 'Juan',
                'last_name' => 'Pérez Gómez',
                'phone' => '809-555-0192',
                'email' => 'juan.perez@empresa.com',
                'institution_department' => 'Tecnología e Infraestructura',
            ]
        );

        $p2 = Participant::firstOrCreate(
            ['document_number' => '001-9876543-2'],
            [
                'first_name' => 'Ana',
                'last_name' => 'Rodríguez Santana',
                'phone' => '829-444-1234',
                'email' => 'ana.rodriguez@empresa.com',
                'institution_department' => 'Recursos Humanos',
            ]
        );

        $p3 = Participant::firstOrCreate(
            ['document_number' => 'EMP-00458'],
            [
                'first_name' => 'Marcos',
                'last_name' => 'Castillo Díaz',
                'phone' => 'Ext. 2451',
                'email' => 'mcastillo@empresa.com',
                'institution_department' => 'Operaciones y Logística',
            ]
        );

        // 4. Asistencia de prueba para el evento 1
        Attendance::firstOrCreate(
            ['event_id' => $evento1->id, 'participant_id' => $p1->id],
            [
                'check_in_at' => now()->subMinutes(35),
                'notes' => 'Registro presencial',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 Chrome/128.0',
            ]
        );

        Attendance::firstOrCreate(
            ['event_id' => $evento1->id, 'participant_id' => $p2->id],
            [
                'check_in_at' => now()->subMinutes(12),
                'notes' => 'Registro QR móvil',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 Mobile Safari',
            ]
        );
    }
}
