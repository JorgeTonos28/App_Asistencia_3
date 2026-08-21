@extends('layouts.admin')

@section('title', $event->title . ($event->isRecurring() ? " ({$event->series_session_label})" : ''))

@section('content')
<div class="space-y-6" x-data="{ 
    signatureModal: false, 
    activeSignatureUrl: '', 
    activeParticipantName: '', 
    missingModal: false 
}">
    <!-- Navegación y Acciones Superiores -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <a href="{{ route('admin.events.index') }}" class="text-xs font-bold text-slate-500 hover:text-brand-600 inline-flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver a eventos
            </a>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $event->title }}</h1>
                <span class="text-xs font-bold px-3 py-1 rounded-full {{ $event->status_badge['class'] }}">
                    {{ $event->status_badge['label'] }}
                </span>
                @if($event->isRecurring())
                    <span class="text-xs font-black px-3 py-1 rounded-full bg-brand-100 text-brand-800 border border-brand-200">
                        {{ $event->series_session_label }}
                    </span>
                @endif
                <!-- Badge de Cierre / Apertura de Registro -->
                <span class="text-xs font-bold px-3 py-1 rounded-full {{ $event->registration_status_info['badge_class'] }}">
                    {{ $event->registration_status_info['message'] }}
                </span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Botón Toggle Apertura / Cierre de Asistencia -->
            <form method="POST" action="{{ route('admin.events.toggle_registration', $event) }}" class="inline">
                @csrf
                @if($event->is_registration_open)
                    <button type="submit" class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold text-xs rounded-xl shadow-xs transition-all inline-flex items-center gap-1.5" title="Pausar o cerrar el registro">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Pausar Registro</span>
                    </button>
                @else
                    <button type="submit" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all inline-flex items-center gap-1.5" title="Reabrir registro manualmente">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Reabrir Registro</span>
                    </button>
                @endif
            </form>

            <!-- Proyección Pública y QR en Vivo -->
            <a href="{{ route('attendance.qr', ['code' => $event->access_code]) }}" target="_blank" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition-all inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>Proyectar QR</span>
            </a>

            <!-- Monitoreo en Vivo -->
            <a href="{{ route('admin.events.live', $event) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all inline-flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                <span>Monitor Admin</span>
            </a>

            <!-- Exportar PDF -->
            <a href="{{ route('admin.events.export_pdf', $event) }}" class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs rounded-xl transition-all inline-flex items-center gap-1.5" title="Descargar Lista Oficial en PDF">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>PDF</span>
            </a>

            <!-- Exportar Excel -->
            <a href="{{ route('admin.events.export_excel', $event) }}" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs rounded-xl transition-all inline-flex items-center gap-1.5" title="Descargar Hoja en Excel">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Excel</span>
            </a>

            @if($event->isRecurring())
                <!-- Exportar Matriz Completa de la Serie -->
                <a href="{{ route('admin.events.export_series_excel', $event) }}" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-800 border border-indigo-200 font-bold text-xs rounded-xl transition-all inline-flex items-center gap-1.5" title="Descargar Matriz Consolidada de Asistencia de toda la serie">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zm0 4h16M9 4v16"/></svg>
                    <span>Matriz Serie Excel</span>
                </a>
            @endif

            <!-- Editar -->
            <a href="{{ route('admin.events.edit', $event) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Editar</span>
            </a>
        </div>
    </div>

    <!-- Si es parte de una serie recurrente: Barra de Navegación entre Sesiones -->
    @if($event->isRecurring())
        <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Sesiones de esta Serie / Taller ({{ $seriesEvents->count() }} Días)
                </span>
                <span class="text-xs text-slate-400 font-medium">Haz clic en cualquier sesión para ver sus firmas o proyectar su QR</span>
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                @foreach($seriesEvents as $s)
                    <a href="{{ route('admin.events.show', $s) }}" 
                       class="flex-shrink-0 px-4 py-2.5 rounded-2xl text-xs font-bold transition-all border flex items-center gap-2 {{ $s->id === $event->id ? 'bg-brand-600 text-white border-brand-600 shadow-md shadow-brand-500/20' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 hover:text-slate-900' }}">
                        <span class="w-5 h-5 rounded-full {{ $s->id === $event->id ? 'bg-white text-brand-700' : 'bg-slate-200 text-slate-700' }} flex items-center justify-center text-[10px] font-black">
                            {{ $s->session_number }}
                        </span>
                        <span>Sesión {{ $s->session_number }} ({{ $s->event_date->format('d/m') }})</span>
                        <span class="text-[11px] opacity-80 font-normal">({{ $s->attendances_count ?? $s->attendances()->count() }} asistencias)</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Tarjeta de Tracking de Retención y Ausencias (si es evento recurrente) -->
    @if($event->isRecurring())
        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white rounded-3xl p-6 shadow-xl border border-slate-700">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs font-bold text-brand-200 border border-white/10">
                        <span>📊 Control de Retención de Asistencia</span>
                        <span>•</span>
                        <span>Referencia: Sesión 1</span>
                    </div>
                    <h3 class="text-xl font-bold tracking-tight">
                        @if($retention['is_base_session'])
                            Sesión Inicial Base de la Serie
                        @else
                            Seguimiento de Asistencia vs Día 1
                        @endif
                    </h3>
                    <p class="text-xs text-slate-300">
                        @if($retention['is_base_session'])
                            Esta es la sesión de apertura. Todos los asistentes aquí registrados definirán la base de retención esperada para las siguientes sesiones.
                        @else
                            Comparando los participantes registrados en este día con los que iniciaron el taller en la Sesión 1.
                        @endif
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white/5 p-4 rounded-2xl border border-white/10">
                    <div class="text-center sm:text-left">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Base Sesión 1</span>
                        <span class="text-2xl font-black text-white">{{ $retention['base_total'] }}</span>
                    </div>

                    <div class="text-center sm:text-left">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Asistieron Hoy</span>
                        <span class="text-2xl font-black text-emerald-400">{{ $retention['current_total'] }}</span>
                    </div>

                    @if(!$retention['is_base_session'])
                        <div class="text-center sm:text-left">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Retención</span>
                            <span class="text-2xl font-black text-brand-300">{{ $retention['retention_rate'] }}%</span>
                        </div>

                        <div class="text-center sm:text-left">
                            <span class="text-[10px] font-bold text-rose-300 uppercase tracking-wider block">Faltantes</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-2xl font-black text-rose-400">{{ $retention['missing_count'] }}</span>
                                @if($retention['missing_count'] > 0)
                                    <button type="button" @click="missingModal = true" class="px-2 py-0.5 bg-rose-500/20 hover:bg-rose-500/30 text-rose-200 border border-rose-500/40 text-[10px] font-bold rounded-lg transition-colors">
                                        Ver quiénes
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Tarjeta de Detalles del Evento -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <div class="space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Fecha y Horario</span>
                <p class="text-sm font-bold text-slate-800">{{ $event->event_date->format('d/m/Y') }}</p>
                <p class="text-xs text-slate-500">
                    @if($event->start_time)
                        {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                        @if($event->end_time) - {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }} @endif
                    @else
                        Horario no especificado
                    @endif
                </p>
            </div>

            <div class="space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Facilitador(es) / Instructor(es)</span>
                <p class="text-sm font-bold text-slate-800">{{ $event->formatted_instructors }}</p>
                <p class="text-xs text-slate-500">{{ $event->location ?? 'Ubicación no especificada' }}</p>
            </div>

            <div class="space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Configuración de Campos</span>
                <p class="text-xs text-slate-700 font-semibold">
                    Cédula: <span class="font-bold {{ $event->require_document ? 'text-rose-600' : 'text-slate-500' }}">{{ $event->require_document ? 'Obligatoria' : 'Opcional' }}</span>
                </p>
                <p class="text-xs text-slate-500">
                    Dpto: {{ match($event->department_mode) { 'required' => 'Obligatorio', 'optional' => 'Opcional', default => 'Oculto' } }}
                </p>
            </div>

            <div class="space-y-1 bg-slate-50 p-4 rounded-2xl border border-slate-100 flex flex-col justify-center">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Asistencias en esta Sesión</span>
                <span class="text-3xl font-black text-brand-600">{{ $totalAttendees }}</span>
            </div>
        </div>

        @if($event->description)
            <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-600">
                <span class="font-bold text-slate-700">Temario / Objetivos:</span> {{ $event->description }}
            </div>
        @endif

        <!-- Enlaces Públicos para compartir (Registro de Participantes y Proyección en Pantalla) -->
        <div class="mt-6 pt-6 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- 1. Enlace para Participantes -->
            <div class="flex flex-col justify-between gap-3 bg-brand-50/50 p-4 rounded-2xl border border-brand-100">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-8 h-8 rounded-lg bg-brand-600 text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <div class="truncate">
                        <span class="text-[11px] font-bold text-brand-900 uppercase tracking-wider block">1. Enlace para Participantes (Sesión {{ $event->session_number }})</span>
                        <a href="{{ route('attendance.form', ['code' => $event->access_code]) }}" target="_blank" class="text-xs text-brand-700 hover:underline font-mono truncate block">
                            {{ route('attendance.form', ['code' => $event->access_code]) }}
                        </a>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-brand-100/60">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ route('attendance.form', ['code' => $event->access_code]) }}'); alert('¡Enlace para participantes copiado!');"
                            class="flex-1 px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 rounded-lg text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>Copiar Enlace</span>
                    </button>
                    <a href="{{ route('attendance.form', ['code' => $event->access_code]) }}" target="_blank" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-1">
                        <span>Abrir</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>

            <!-- 2. Enlace de Proyección Pública -->
            <div class="flex flex-col justify-between gap-3 bg-slate-900 text-white p-4 rounded-2xl border border-slate-800">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <div class="truncate">
                        <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider block">2. Enlace de Proyección Pública (Sin Login)</span>
                        <a href="{{ route('attendance.qr', ['code' => $event->access_code]) }}" target="_blank" class="text-xs text-slate-300 hover:text-white hover:underline font-mono truncate block">
                            {{ route('attendance.qr', ['code' => $event->access_code]) }}
                        </a>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-slate-800">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ route('attendance.qr', ['code' => $event->access_code]) }}'); alert('¡Enlace de proyección pública copiado!');"
                            class="flex-1 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-lg text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>Copiar Enlace Proyección</span>
                    </button>
                    <a href="{{ route('attendance.qr', ['code' => $event->access_code]) }}" target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-1">
                        <span>Proyectar</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Participantes Registrados en esta sesión -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Listado de Asistencia Registrada en esta Sesión ({{ $totalAttendees }})</h2>
                <p class="text-xs text-slate-500">Participantes que han completado el formulario y firmado</p>
            </div>

            <form method="GET" action="{{ route('admin.events.show', $event) }}" class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por código, cédula o nombre..."
                           class="pl-9 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 outline-none w-48 sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-xl text-xs font-semibold">Buscar</button>
                @if(request('search'))
                    <a href="{{ route('admin.events.show', $event) }}" class="px-2 py-1.5 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">Limpiar</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-4 text-center w-12">#</th>
                        <th class="py-3.5 px-4">Código</th>
                        <th class="py-3.5 px-4">Cédula</th>
                        <th class="py-3.5 px-4">Nombres y Apellidos</th>
                        <th class="py-3.5 px-4">Teléfono / Ext.</th>
                        <th class="py-3.5 px-4">Departamento / Área</th>
                        <th class="py-3.5 px-4 text-center">Firma Digital</th>
                        <th class="py-3.5 px-4">Fecha y Hora</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($attendances as $index => $attendance)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 text-center font-bold text-slate-400">
                                {{ $attendances->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                {{ $attendance->participant->employee_code ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-600">
                                {{ $attendance->participant->document_number ?? '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.participants.show', $attendance->participant) }}" class="font-bold text-slate-900 hover:text-brand-600">
                                    {{ $attendance->participant->full_name }}
                                </a>
                                @if($attendance->participant->email)
                                    <span class="block text-[11px] text-slate-400">{{ $attendance->participant->email }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                {{ $attendance->participant->phone ?? '-' }}
                            </td>
                            <td class="py-3 px-4">
                                {{ $attendance->participant->institution_department ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($attendance->signature_url)
                                    <button type="button" @click="activeSignatureUrl = '{{ $attendance->signature_url }}'; activeParticipantName = '{{ $attendance->participant->full_name }}'; signatureModal = true" 
                                            class="inline-block p-1 bg-white border border-slate-200 rounded-lg hover:border-brand-500 hover:shadow-md transition-all group">
                                        <img src="{{ $attendance->signature_url }}" alt="Firma" class="h-8 max-w-[100px] object-contain group-hover:scale-105 transition-transform">
                                    </button>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Sin firma</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                <span class="font-medium text-slate-800">{{ $attendance->check_in_at->format('d/m/Y') }}</span>
                                <span class="block text-[11px] text-slate-400">{{ $attendance->check_in_at->format('h:i:s A') }}</span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form method="POST" action="{{ route('admin.events.attendances.destroy', [$event, $attendance]) }}" onsubmit="return confirm('¿Estás seguro de que deseas quitar a {{ addslashes($attendance->participant->full_name) }} de la lista de asistencia de este evento?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl border border-rose-100 transition-all" title="Quitar de la lista de asistencia">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Quitar</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                No se encontraron registros de asistencia para esta sesión.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

    <!-- Modal para Visualización de Firma -->
    <div x-show="signatureModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.outside="signatureModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Firma Digital Capturada</h3>
                    <p class="text-xs text-slate-500" x-text="activeParticipantName"></p>
                </div>
                <button @click="signatureModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center min-h-[160px]">
                <img :src="activeSignatureUrl" alt="Firma Ampliada" class="max-h-40 max-w-full object-contain">
            </div>
            <div class="text-right">
                <button type="button" @click="signatureModal = false" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal de Participantes Faltantes (Ausentes vs Sesión 1) -->
    @if($event->isRecurring() && !$retention['is_base_session'])
        <div x-show="missingModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm" x-cloak>
            <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[85vh] flex flex-col" @click.outside="missingModal = false">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-shrink-0">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <span>Participantes Ausentes en esta Sesión ({{ $missingAttendees->count() }})</span>
                        </h3>
                        <p class="text-xs text-slate-500">Asistieron a la Sesión 1 pero aún no han firmado en la Sesión {{ $event->session_number }}</p>
                    </div>
                    <button @click="missingModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 divide-y divide-slate-100">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider">
                                <th class="py-2.5 px-3">Código</th>
                                <th class="py-2.5 px-3">Cédula</th>
                                <th class="py-2.5 px-3">Nombre Completo</th>
                                <th class="py-2.5 px-3">Teléfono</th>
                                <th class="py-2.5 px-3">Departamento</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($missingAttendees as $m)
                                <tr>
                                    <td class="py-2.5 px-3 font-mono font-bold">{{ $m->employee_code ?? '-' }}</td>
                                    <td class="py-2.5 px-3 font-mono text-slate-600">{{ $m->document_number ?? '-' }}</td>
                                    <td class="py-2.5 px-3 font-bold text-slate-900">{{ $m->full_name }}</td>
                                    <td class="py-2.5 px-3">{{ $m->phone ?? '-' }}</td>
                                    <td class="py-2.5 px-3">{{ $m->institution_department ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-emerald-600 font-bold">
                                        ¡Excelente! Todos los participantes de la Sesión 1 han firmado en esta sesión (100% de asistencia retenida).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between flex-shrink-0">
                    <span class="text-xs text-slate-500">Total Faltantes: <strong class="text-rose-600">{{ $missingAttendees->count() }}</strong></span>
                    <button type="button" @click="missingModal = false" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
