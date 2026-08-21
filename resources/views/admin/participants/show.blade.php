@extends('layouts.admin')

@section('title', 'Perfil de Participante - ' . $participant->full_name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.participants.index') }}" class="text-xs font-bold text-slate-500 hover:text-brand-600 inline-flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al directorio
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $participant->full_name }}</h1>
        </div>
        <a href="{{ route('admin.participants.edit', $participant) }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all">
            Editar Información
        </a>
    </div>

    <!-- Datos del Participante -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Información de Contacto</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Cédula / Código</span>
                <span class="text-base font-mono font-bold text-slate-900">{{ $participant->document_number }}</span>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Teléfono / Extensión</span>
                <span class="text-sm font-semibold text-slate-800">{{ $participant->phone ?? 'No registrado' }}</span>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Correo Electrónico</span>
                <span class="text-sm text-slate-800">{{ $participant->email ?? 'No registrado' }}</span>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Departamento / Institución</span>
                <span class="text-sm text-slate-800">{{ $participant->institution_department ?? 'No registrado' }}</span>
            </div>
        </div>
    </div>

    <!-- Historial de Asistencia a Eventos -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-900">Historial de Eventos Asistidos ({{ $participant->attendances->count() }})</h2>
            <p class="text-xs text-slate-500">Listado cronológico de capacitaciones en las que ha participado</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-4 text-center w-12">#</th>
                        <th class="py-3.5 px-4">Evento / Capacitación</th>
                        <th class="py-3.5 px-4">Fecha del Evento</th>
                        <th class="py-3.5 px-4">Facilitador</th>
                        <th class="py-3.5 px-4 text-center">Firma</th>
                        <th class="py-3.5 px-4">Hora de Registro</th>
                        <th class="py-3.5 px-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($participant->attendances as $index => $attendance)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <a href="{{ route('admin.events.show', $attendance->event) }}" class="hover:text-brand-600">
                                    {{ $attendance->event->title }}
                                </a>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $attendance->event->event_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $attendance->event->instructor ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-center">
                                @if($attendance->signature_url)
                                    <img src="{{ $attendance->signature_url }}" alt="Firma" class="h-7 max-w-[80px] object-contain mx-auto">
                                @else
                                    <span class="text-slate-400 italic">Sin firma</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $attendance->check_in_at->format('d/m/Y h:i A') }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.events.show', $attendance->event) }}" class="px-2.5 py-1 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold rounded-lg text-xs">
                                    Ver Evento
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                Este participante no cuenta con asistencias registradas aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
