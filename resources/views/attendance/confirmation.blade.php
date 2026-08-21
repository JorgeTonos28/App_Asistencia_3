@extends('layouts.guest')

@section('title', '¡Asistencia Confirmada! - ' . $event->title)

@section('content')
<div class="max-w-xl mx-auto my-6">
    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-xl text-center space-y-6">
        <!-- Ícono de Éxito -->
        <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-inner">
            <svg class="w-10 h-10 animate-scale" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <div class="space-y-1">
            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full uppercase tracking-wider">
                Registro Exitoso
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">¡Asistencia Registrada!</h1>
            <p class="text-xs sm:text-sm text-slate-500">Gracias por tu participación. Tu presencia y firma han sido guardadas.</p>
        </div>

        <!-- Tarjeta de Comprobante -->
        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 text-left space-y-4">
            <div class="border-b border-slate-200 pb-3">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Evento / Capacitación</span>
                <h3 class="text-sm font-bold text-slate-900">{{ $event->title }}</h3>
                <p class="text-xs text-slate-500">{{ $event->event_date->format('d/m/Y') }} @if($event->instructor) · {{ $event->instructor }} @endif</p>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Participante</span>
                    <span class="font-bold text-slate-900">{{ $attendance->participant->full_name }}</span>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Cédula / Código</span>
                    <span class="font-mono font-bold text-slate-800">{{ $attendance->participant->document_number }}</span>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Teléfono / Extensión</span>
                    <span class="text-slate-700">{{ $attendance->participant->phone ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Fecha y Hora de Registro</span>
                    <span class="text-slate-700 font-semibold">{{ $attendance->check_in_at->format('d/m/Y h:i:s A') }}</span>
                </div>
            </div>

            @if($attendance->signature_url)
                <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Firma Registrada</span>
                        <span class="text-[10px] text-emerald-600 font-bold">Válida y Almacenada</span>
                    </div>
                    <div class="p-1 bg-white border border-slate-200 rounded-lg">
                        <img src="{{ $attendance->signature_url }}" alt="Firma Digital" class="h-10 max-w-[120px] object-contain">
                    </div>
                </div>
            @endif
        </div>

        <!-- Acciones -->
        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('attendance.form', ['code' => $event->access_code]) }}" class="w-full sm:w-auto px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                Registrar a Otro Participante
            </a>
        </div>
    </div>
</div>
@endsection
