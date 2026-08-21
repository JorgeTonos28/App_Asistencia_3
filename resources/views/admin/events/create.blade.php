@extends('layouts.admin')

@section('title', 'Crear Nuevo Evento')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="eventCreateForm()">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.events.index') }}" class="text-xs font-bold text-slate-500 hover:text-brand-600 inline-flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver a la lista
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Crear Nuevo Evento o Curso</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.events.store') }}" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        @csrf

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
                @foreach($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- 1. Información Principal -->
        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">Información Principal</h2>

            <div>
                <label for="title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nombre del Evento / Curso / Taller <span class="text-rose-500">*</span></label>
                <input type="text" id="title" name="title" x-model="title" required placeholder="Ej: Taller Práctico de Innovación y Metodologías Ágiles"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="access_code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Código de Acceso (Sesión 1)</label>
                    <div class="relative">
                        <input type="text" id="access_code" name="access_code" value="{{ $defaultCode }}" readonly
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-mono font-bold text-brand-700 text-sm cursor-not-allowed outline-none select-all">
                        <span class="absolute right-2.5 top-2.5 text-[10px] bg-emerald-100 text-emerald-800 font-black uppercase px-2 py-0.5 rounded-full">
                            ⚡ Auto
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Código generado automáticamente para el QR y enlace.</p>
                </div>

                <!-- Facilitadores / Instructores Múltiples -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Facilitadores / Expositores
                    </label>
                    <div class="space-y-2">
                        <div class="flex gap-2">
                            <input type="text" x-model="newInstructor" @keydown.enter.prevent="addInstructor()"
                                   placeholder="Ej: Ing. Laura Morales (Presiona Enter)"
                                   class="flex-1 px-4 py-2 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                            <button type="button" @click="addInstructor()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">
                                + Agregar
                            </button>
                        </div>
                        <!-- Lista de tags de facilitadores -->
                        <div class="flex flex-wrap gap-1.5 min-h-[30px] p-2 bg-slate-50 rounded-xl border border-slate-200/70">
                            <template x-for="(inst, idx) in instructors" :key="idx">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-800 text-xs font-semibold shadow-xs">
                                    <span x-text="inst"></span>
                                    <button type="button" @click="removeInstructor(idx)" class="text-slate-400 hover:text-rose-600 font-bold">&times;</button>
                                    <input type="hidden" name="instructors[]" :value="inst">
                                </span>
                            </template>
                            <template x-if="instructors.length === 0">
                                <span class="text-xs text-slate-400 italic">No hay facilitadores asignados aún.</span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Descripción u Objetivos</label>
                <textarea id="description" name="description" x-model="description" rows="3" placeholder="Detalles, temario u observaciones generales del evento..."
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">{{ old('description') }}</textarea>
            </div>
        </div>

        <!-- 2. Fecha, Horario y Lugar (Sesión 1) -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    <span x-show="!isRecurring">Fecha, Horario y Lugar</span>
                    <span x-show="isRecurring">Sesión 1 (Día Inicial)</span>
                </h2>
                <span x-show="isRecurring" class="text-xs font-bold px-2 py-0.5 rounded-md bg-brand-50 text-brand-700 border border-brand-200">
                    Sesión Base #1
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="event_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Fecha <span class="text-rose-500">*</span></label>
                    <input type="date" id="event_date" name="event_date" x-model="eventDate" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                </div>

                <div>
                    <label for="start_time" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Hora Inicio</label>
                    <input type="time" id="start_time" name="start_time" x-model="startTime"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                </div>

                <div>
                    <label for="end_time" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Hora Fin (Cierre Automático)</label>
                    <input type="time" id="end_time" name="end_time" x-model="endTime"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                </div>
            </div>

            <div>
                <label for="location" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ubicación / Salón / Modalidad</label>
                <input type="text" id="location" name="location" x-model="location" placeholder="Ej: Auditorio Central / Virtual vía Teams"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
            </div>
        </div>

        <!-- 3. Módulo de Recurrencia y Multisesión -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
            <div class="bg-gradient-to-r from-brand-50 to-indigo-50/50 p-5 rounded-2xl border border-brand-100">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_recurring" value="1" x-model="isRecurring"
                                   class="w-5 h-5 rounded-lg border-brand-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm font-black text-brand-950">¿Es un evento recurrente / de múltiples días o sesiones?</span>
                        </label>
                        <p class="text-xs text-brand-800/80 ml-8">
                            Habilita la creación de series (Día 2, Día 3...). Cada sesión tendrá su propio código QR y el sistema realizará tracking automático de retención y ausencias en comparación con el primer día.
                        </p>
                    </div>
                </div>

                <!-- Lista dinámica de sesiones adicionales -->
                <div x-show="isRecurring" x-collapse class="mt-5 space-y-4 pt-4 border-t border-brand-200/60">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wider">Sesiones Adicionales de la Serie</h3>
                        <button type="button" @click="addSession()" class="px-3.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>+ Agregar Otra Sesión / Día</span>
                        </button>
                    </div>

                    <template x-for="(session, index) in sessions" :key="index">
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4 relative">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-brand-600 text-white font-black text-xs flex items-center justify-center" x-text="index + 2"></span>
                                    <span class="text-xs font-extrabold text-slate-900 uppercase tracking-wide" x-text="'Sesión #' + (index + 2)"></span>
                                </div>
                                <button type="button" @click="removeSession(index)" class="text-xs text-rose-600 hover:text-rose-800 font-bold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Eliminar Sesión</span>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Fecha <span class="text-rose-500">*</span></label>
                                    <input type="date" :name="'sessions[' + index + '][event_date]'" x-model="session.event_date" required
                                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Hora Inicio</label>
                                    <input type="time" :name="'sessions[' + index + '][start_time]'" x-model="session.start_time"
                                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Hora Fin (Cierre Auto)</label>
                                    <input type="time" :name="'sessions[' + index + '][end_time]'" x-model="session.end_time"
                                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Ubicación de esta sesión</label>
                                    <input type="text" :name="'sessions[' + index + '][location]'" x-model="session.location" placeholder="Misma que la principal o personalizada"
                                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Facilitador(es) de esta sesión</label>
                                    <input type="text" :name="'sessions[' + index + '][instructor]'" x-model="session.instructor" placeholder="Mismo o escribe separados por coma"
                                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Temario u Observaciones específicas de esta sesión</label>
                                <input type="text" :name="'sessions[' + index + '][description]'" x-model="session.description" placeholder="Ej: Módulo II: Aplicación en campo"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- 4. Campos del Formulario y Configuración -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">Campos del Formulario y Configuración</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Requisito de Cédula -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                    <span class="text-xs font-bold text-slate-800 block mb-1">Campo de Cédula</span>
                    <p class="text-[11px] text-slate-500 mb-3">El código de empleado siempre es obligatorio.</p>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="require_document" value="1" {{ old('require_document') ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-xs font-bold text-slate-700">Hacer Cédula Obligatoria</span>
                    </label>
                </div>

                <!-- Requisito de Departamento -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                    <label for="department_mode" class="text-xs font-bold text-slate-800 block mb-1">Campo de Departamento / Área</label>
                    <p class="text-[11px] text-slate-500 mb-2">Visibilidad en el formulario para participantes.</p>
                    <select id="department_mode" name="department_mode" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                        <option value="hidden" {{ old('department_mode', 'hidden') === 'hidden' ? 'selected' : '' }}>Ocultar campo</option>
                        <option value="optional" {{ old('department_mode') === 'optional' ? 'selected' : '' }}>Mostrar como Opcional</option>
                        <option value="required" {{ old('department_mode') === 'required' ? 'selected' : '' }}>Mostrar como Obligatorio</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Estado Inicial</label>
                    <select id="status" name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>En Curso / Activo</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Finalizado</option>
                        <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="allow_registration" value="1" {{ old('allow_registration', true) ? 'checked' : '' }}
                               class="w-5 h-5 rounded-lg border-slate-300 text-brand-600 focus:ring-brand-500">
                        <div>
                            <span class="text-sm font-bold text-slate-800">Habilitar Registro de Asistencia</span>
                            <p class="text-[11px] text-slate-500">Se cerrará automáticamente al llegar la hora fin configurada.</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('admin.events.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-600/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Guardar y Publicar Evento</span>
            </button>
        </div>
    </form>
</div>

<script>
function eventCreateForm() {
    return {
        title: @json(old('title', '')),
        description: @json(old('description', '')),
        eventDate: @json(old('event_date', date('Y-m-d'))),
        startTime: @json(old('start_time', '09:00')),
        endTime: @json(old('end_time', '12:00')),
        location: @json(old('location', '')),
        instructors: @json(old('instructors', [])),
        newInstructor: '',
        isRecurring: @json(old('is_recurring', false)),
        sessions: @json(old('sessions', [])),

        addInstructor() {
            const name = this.newInstructor.trim();
            if (name.length > 0 && !this.instructors.includes(name)) {
                this.instructors.push(name);
                this.newInstructor = '';
            }
        },

        removeInstructor(index) {
            this.instructors.splice(index, 1);
        },

        addSession() {
            // Calcular fecha tentativa del siguiente día a partir de la última sesión
            let nextDate = this.eventDate;
            if (this.sessions.length > 0) {
                nextDate = this.sessions[this.sessions.length - 1].event_date || this.eventDate;
            }

            let d = new Date(nextDate + 'T00:00:00');
            d.setDate(d.getDate() + 1);
            let nextDateStr = d.toISOString().split('T')[0];

            this.sessions.push({
                event_date: nextDateStr,
                start_time: this.startTime,
                end_time: this.endTime,
                location: this.location,
                instructor: this.instructors.join(', '),
                description: ''
            });
        },

        removeSession(index) {
            this.sessions.splice(index, 1);
        }
    };
}
</script>
@endsection
