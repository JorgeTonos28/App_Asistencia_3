@extends('layouts.admin')

@section('title', 'Dashboard General')

@section('content')
<div class="space-y-8">
    <!-- Header de bienvenida -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-semibold mb-2">
                <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                Sistema de Control Activo
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Panel de Control</h1>
            <p class="text-sm text-slate-500 mt-1">Supervisa las asistencias en vivo, crea nuevos eventos y genera reportes.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.events.create') }}" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm rounded-xl shadow-md shadow-brand-600/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nuevo Evento / Curso</span>
            </a>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas (KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Eventos Totales -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-brand-500/50 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Eventos</span>
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-slate-900">{{ $stats['total_events'] }}</span>
                <p class="text-xs text-slate-500 mt-1">Registrados en la plataforma</p>
            </div>
        </div>

        <!-- Eventos Activos -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-emerald-500/50 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Eventos Activos</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-emerald-600">{{ $stats['active_events'] }}</span>
                <p class="text-xs text-slate-500 mt-1">Disponibles para registro</p>
            </div>
        </div>

        <!-- Participantes Únicos -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-500/50 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Participantes BD</span>
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-indigo-600">{{ $stats['total_participants'] }}</span>
                <p class="text-xs text-slate-500 mt-1">Registrados en catálogo</p>
            </div>
        </div>

        <!-- Asistencias Totales -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-amber-500/50 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Asistencias Totales</span>
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900">{{ $stats['total_attendances'] }}</span>
                    @if($stats['today_attendances'] > 0)
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">+{{ $stats['today_attendances'] }} hoy</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1">Firmas recolectadas</p>
            </div>
        </div>
    </div>

    <!-- Sección Dividida: Eventos Activos y Flujo en Vivo -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Eventos Activos y en Curso (2 Columnas) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Eventos Activos y Próximos</h2>
                    <p class="text-xs text-slate-500">Accede rápidamente a la vista en vivo, QR o reportes</p>
                </div>
                <a href="{{ route('admin.events.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                    Ver todos los eventos
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="space-y-4">
                @forelse($activeEvents as $event)
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition-all">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $event->access_code }}
                                    </span>
                                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $event->status_badge['class'] }}">
                                        {{ $event->status_badge['label'] }}
                                    </span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 hover:text-brand-600">
                                    <a href="{{ route('admin.events.show', $event) }}">{{ $event->title }}</a>
                                </h3>
                                <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ $event->event_date->format('d/m/Y') }}
                                    </span>
                                    @if($event->instructor)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            {{ $event->instructor }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right sm:border-l sm:pl-5 sm:border-slate-100 flex-shrink-0">
                                <span class="text-2xl font-black text-slate-900">{{ $event->attendances_count }}</span>
                                <span class="block text-[11px] font-medium text-slate-400 uppercase tracking-wide">Asistencias</span>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 pt-1">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.events.live', $event) }}" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Ver en Vivo
                                </a>
                                <a href="{{ route('admin.events.qr', $event) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    Código QR
                                </a>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.events.export_pdf', $event) }}" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Exportar PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </a>
                                <a href="{{ route('admin.events.export_excel', $event) }}" class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Exportar Excel">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                                <a href="{{ route('admin.events.show', $event) }}" class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 rounded-lg text-xs font-bold transition-colors">
                                    Ver Detalle &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white rounded-3xl border border-slate-200">
                        <p class="text-sm text-slate-500">No hay eventos activos en este momento.</p>
                        <a href="{{ route('admin.events.create') }}" class="mt-3 inline-block text-xs font-bold text-brand-600 hover:underline">Crear el primer evento</a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Flujo de Asistencias Recientes (1 Columna) -->
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Últimos Registros</h2>
                    <p class="text-xs text-slate-500">Actividad reciente en el sistema</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm space-y-4 divide-y divide-slate-100">
                @forelse($recentAttendances as $attendance)
                    <div class="pt-3 first:pt-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                                    {{ substr($attendance->participant->first_name, 0, 1) }}{{ substr($attendance->participant->last_name, 0, 1) }}
                                </div>
                                <div class="space-y-0.5">
                                    <p class="text-xs font-bold text-slate-900 leading-tight">{{ $attendance->participant->full_name }}</p>
                                    <p class="text-[11px] text-slate-500 font-mono">{{ $attendance->participant->document_number }}</p>
                                    <p class="text-[11px] text-brand-600 font-medium truncate max-w-[170px]">{{ $attendance->event->title }}</p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-[11px] font-semibold text-slate-400 block">{{ $attendance->check_in_at->diffForHumans() }}</span>
                                @if($attendance->signature_url)
                                    <span class="inline-block mt-1 text-[10px] bg-slate-100 text-slate-600 font-medium px-1.5 py-0.5 rounded">Firmado</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-400">
                        Aún no se han registrado asistencias el día de hoy.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
