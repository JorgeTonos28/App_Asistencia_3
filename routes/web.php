<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistema de Asistencia
|--------------------------------------------------------------------------
*/

// Redirección principal al login del panel o listado
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Portal Público de Registro de Asistencia
Route::get('/event/{code}', [AttendanceController::class, 'showForm'])->name('attendance.form');
Route::post('/event/{code}', [AttendanceController::class, 'register'])->name('attendance.register');
Route::get('/event/{code}/confirmation/{attendance}', [AttendanceController::class, 'confirmation'])->name('attendance.confirmation');

// Pantalla Pública de Proyección de Código QR y Registros en Vivo (Accesible sin login para proyectar)
Route::get('/event/{code}/qr', [AttendanceController::class, 'publicQrProjection'])->name('attendance.qr');
Route::get('/event/{code}/live-feed', [AttendanceController::class, 'publicLiveFeed'])->name('attendance.live_feed');

// Endpoint AJAX para autocompletado de participantes por código o cédula
Route::get('/api/participants/lookup', [AttendanceController::class, 'lookupParticipant'])->name('participants.lookup');

// Autenticación de Administradores
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Panel Administrativo (Protegido por Middleware Auth)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil y Configuración del Usuario Autenticado
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Gestión de Eventos
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/events/{event}/toggle-registration', [EventController::class, 'toggleRegistration'])->name('events.toggle_registration');
    Route::post('/events/{event}/sessions', [EventController::class, 'storeSession'])->name('events.sessions.store');
    Route::delete('/events/{event}/attendances/{attendance}', [EventController::class, 'destroyAttendance'])->name('events.attendances.destroy');

    // Funciones Especiales de Eventos
    Route::get('/events/{event}/live', [EventController::class, 'live'])->name('events.live');
    Route::get('/events/{event}/live-feed', [EventController::class, 'liveFeed'])->name('events.live_feed');
    Route::get('/events/{event}/qr', [EventController::class, 'qr'])->name('events.qr');
    Route::get('/events/{event}/export-pdf', [EventController::class, 'exportPdf'])->name('events.export_pdf');
    Route::get('/events/{event}/export-excel', [EventController::class, 'exportExcel'])->name('events.export_excel');
    Route::get('/events/{event}/export-series-excel', [EventController::class, 'exportSeriesExcel'])->name('events.export_series_excel');

    // Gestión de Participantes
    Route::get('/participants', [ParticipantController::class, 'index'])->name('participants.index');
    Route::get('/participants/{participant}', [ParticipantController::class, 'show'])->name('participants.show');
    Route::get('/participants/{participant}/edit', [ParticipantController::class, 'edit'])->name('participants.edit');
    Route::put('/participants/{participant}', [ParticipantController::class, 'update'])->name('participants.update');
    Route::delete('/participants/{participant}', [ParticipantController::class, 'destroy'])->name('participants.destroy');

    // Gestión de Usuarios y Roles (Solo Super Admin)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});
