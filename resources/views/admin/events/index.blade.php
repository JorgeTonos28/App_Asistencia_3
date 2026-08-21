@extends('layouts.admin')

@section('title', 'Gestión de Eventos y Cursos')

@section('content')
<div class="space-y-6">
    <!-- Header y Acciones -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Eventos y Cursos</h1>
            <p class="text-xs text-slate-500 mt-1">Administra capacitaciones, genera enlaces de registro y exporta reportes</p>
        </div>
        <div>
            <a href="{{ route('admin.events.create') }}" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Crear Nuevo Evento</span>
            </a>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.events.index') }}" class="flex-1 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por título, facilitador, código o ubicación..." 
                       class="w-full pl-10 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 outline-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div class="w-full sm:w-48">
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 outline-none text-slate-700">
                    <option value="">Todos los Estados</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>En Curso / Activos</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Finalizados</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelados</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold">
                Filtrar
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.events.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold flex items-center justify-center">
                    Limpiar
                </a>
            @endif
        </form>
    </div>

    <!-- Listado de Eventos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($events as $event)
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between overflow-hidden group">
                <div class="p-6">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $event->access_code }}
                        </span>
                        <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full {{ $event->status_badge['class'] }}">
                            {{ $event->status_badge['label'] }}
                        </span>
                    </div>

                    <h3 class="font-bold text-slate-900 text-base leading-snug line-clamp-2 hover:text-brand-600">
                        <a href="{{ route('admin.events.show', $event) }}">{{ $event->title }}</a>
                    </h3>

                    @if($event->description)
                        <p class="text-xs text-slate-500 mt-2 line-clamp-2">{{ $event->description }}</p>
                    @endif

                    <div class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-xs text-slate-600">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ $event->event_date->format('d/m/Y') }} @if($event->start_time) · {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }} @endif</span>
                        </div>

                        @if($event->instructor)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span class="truncate">{{ $event->instructor }}</span>
                            </div>
                        @endif

                        @if($event->location)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="truncate">{{ $event->location }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-3.5 border-t border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="text-lg font-black text-slate-900">{{ $event->attendances_count }}</span>
                        <span class="text-[11px] font-medium text-slate-500">Asistentes</span>
                    </div>

                    <div class="flex items-center gap-1">
                        <a href="{{ route('admin.events.live', $event) }}" class="p-1.5 text-emerald-600 hover:bg-emerald-100 rounded-lg" title="Monitoreo en Vivo">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </a>
                        <a href="{{ route('attendance.qr', ['code' => $event->access_code]) }}" target="_blank" class="p-1.5 text-slate-600 hover:bg-slate-200 rounded-lg" title="Proyectar Código QR en Vivo (Público)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </a>
                        <a href="{{ route('admin.events.export_pdf', $event) }}" class="p-1.5 text-rose-600 hover:bg-rose-100 rounded-lg" title="PDF">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </a>
                        <a href="{{ route('admin.events.export_excel', $event) }}" class="p-1.5 text-emerald-700 hover:bg-emerald-100 rounded-lg" title="Excel">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </a>
                        <a href="{{ route('admin.events.show', $event) }}" class="ml-1 px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-xs font-bold transition-all">
                            Detalle
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-slate-200">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">No se encontraron eventos</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">No hay eventos que coincidan con los criterios de búsqueda o aún no has creado ninguno.</p>
                <a href="{{ route('admin.events.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white text-xs font-bold rounded-xl shadow-sm hover:bg-brand-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Crear Primer Evento
                </a>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    @if($events->hasPages())
        <div class="mt-6">
            {{ $events->links() }}
        </div>
    @endif
</div>
@endsection
