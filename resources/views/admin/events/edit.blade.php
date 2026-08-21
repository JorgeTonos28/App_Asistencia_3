@extends('layouts.admin')

@section('title', 'Editar Evento')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.events.show', $event) }}" class="text-xs font-bold text-slate-500 hover:text-brand-600 inline-flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al detalle
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Editar Evento / Curso</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.events.update', $event) }}" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        @csrf
        @method('PUT')

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
                @foreach($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">Información Principal</h2>

            <div>
                <label for="title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nombre del Evento / Curso <span class="text-rose-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $event->title) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
            </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Código de Acceso (Único)</label>
                    <div class="px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-mono font-bold text-sm flex items-center justify-between">
                        <span>{{ $event->access_code }}</span>
                        <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full font-sans font-bold">Fijo</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">El código no puede modificarse para proteger los enlaces y QR existentes.</p>
                </div>

                <div>
                    <label for="instructor" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Facilitador / Instructor</label>
                    <input type="text" id="instructor" name="instructor" value="{{ old('instructor', $event->instructor) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Descripción u Objetivos</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">{{ old('description', $event->description) }}</textarea>
            </div>
        </div>

        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">Fecha, Horario y Lugar</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="event_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Fecha <span class="text-rose-500">*</span></label>
                    <input type="date" id="event_date" name="event_date" value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                </div>

                <div>
                    <label for="start_time" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Hora Inicio</label>
                    <input type="time" id="start_time" name="start_time" value="{{ old('start_time', $event->start_time ? substr($event->start_time, 0, 5) : '') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                </div>

                <div>
                    <label for="end_time" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Hora Fin</label>
                    <input type="time" id="end_time" name="end_time" value="{{ old('end_time', $event->end_time ? substr($event->end_time, 0, 5) : '') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                </div>
            </div>

            <div>
                <label for="location" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ubicación / Salón / Modalidad</label>
                <input type="text" id="location" name="location" value="{{ old('location', $event->location) }}"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
            </div>
        </div>

        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">Campos del Formulario y Configuración</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Requisito de Cédula -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                    <span class="text-xs font-bold text-slate-800 block mb-1">Campo de Cédula</span>
                    <p class="text-[11px] text-slate-500 mb-3">El código de empleado siempre es obligatorio.</p>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="require_document" value="1" {{ old('require_document', $event->require_document) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-xs font-bold text-slate-700">Hacer Cédula Obligatoria</span>
                    </label>
                </div>

                <!-- Requisito de Departamento -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                    <label for="department_mode" class="text-xs font-bold text-slate-800 block mb-1">Campo de Departamento / Área</label>
                    <p class="text-[11px] text-slate-500 mb-2">Visibilidad en el formulario para participantes.</p>
                    <select id="department_mode" name="department_mode" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                        <option value="hidden" {{ old('department_mode', $event->department_mode) === 'hidden' ? 'selected' : '' }}>Ocultar campo</option>
                        <option value="optional" {{ old('department_mode', $event->department_mode) === 'optional' ? 'selected' : '' }}>Mostrar como Opcional</option>
                        <option value="required" {{ old('department_mode', $event->department_mode) === 'required' ? 'selected' : '' }}>Mostrar como Obligatorio</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Estado del Evento</label>
                    <select id="status" name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                        <option value="active" {{ old('status', $event->status) === 'active' ? 'selected' : '' }}>En Curso / Activo</option>
                        <option value="completed" {{ old('status', $event->status) === 'completed' ? 'selected' : '' }}>Finalizado</option>
                        <option value="cancelled" {{ old('status', $event->status) === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="allow_registration" value="1" {{ old('allow_registration', $event->allow_registration) ? 'checked' : '' }}
                               class="w-5 h-5 rounded-lg border-slate-300 text-brand-600 focus:ring-brand-500">
                        <div>
                            <span class="text-sm font-bold text-slate-800">Permitir Registro de Asistencia</span>
                            <p class="text-[11px] text-slate-500">Habilita el formulario público y escaneo QR</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
            <button type="button" onclick="if(confirm('¿Estás seguro de eliminar este evento y todos sus registros de asistencia?')) document.getElementById('delete-form').submit();" class="text-xs font-bold text-rose-600 hover:text-rose-700">
                Eliminar Evento
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.events.show', $event) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-600/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Guardar Cambios</span>
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" action="{{ route('admin.events.destroy', $event) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
