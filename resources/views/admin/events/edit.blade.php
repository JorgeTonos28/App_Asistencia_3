@extends('layouts.admin')

@section('title', 'Editar Evento')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="eventEditForm()">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.events.show', $event) }}" class="text-xs font-bold text-slate-500 hover:text-brand-600 inline-flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al detalle
            </a>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Editar Evento / Curso</h1>
                @if($event->isRecurring())
                    <span class="text-xs font-black px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 border border-brand-200">
                        {{ $event->series_session_label }}
                    </span>
                @endif
            </div>
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Código de Acceso (Único)</label>
                    <div class="px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-mono font-bold text-sm flex items-center justify-between">
                        <span>{{ $event->access_code }}</span>
                        <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full font-sans font-bold">Fijo</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">El código no puede modificarse para proteger los enlaces y QR existentes.</p>
                </div>

                <!-- Facilitadores Múltiples -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Facilitador(es) / Instructor(es)</label>
                    <div class="space-y-2">
                        <div class="flex gap-2">
                            <input type="text" x-model="newInstructor" @keydown.enter.prevent="addInstructor()"
                                   placeholder="Escribe nombre y pulsa Enter"
                                   class="flex-1 px-4 py-2 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                            <button type="button" @click="addInstructor()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">
                                + Agregar
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-1.5 min-h-[30px] p-2 bg-slate-50 rounded-xl border border-slate-200/70">
                            <template x-for="(inst, idx) in instructors" :key="idx">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-800 text-xs font-semibold shadow-xs">
                                    <span x-text="inst"></span>
                                    <button type="button" @click="removeInstructor(idx)" class="text-slate-400 hover:text-rose-600 font-bold">&times;</button>
                                    <input type="hidden" name="instructors[]" :value="inst">
                                </span>
                            </template>
                            <template x-if="instructors.length === 0">
                                <span class="text-xs text-slate-400 italic">Sin facilitadores asignados.</span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Descripción u Objetivos</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">{{ old('description', $event->description) }}</textarea>
            </div>
        </div>

        <div class="space-y-4 pt-4 border-t border-slate-100">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                @if($event->isRecurring())
                    Fecha, Horario y Salón de esta Sesión (#{{ $event->session_number }})
                @else
                    Fecha, Horario y Lugar
                @endif
            </h2>

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
                    <label for="end_time" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Hora Fin (Cierre Auto)</label>
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

        <!-- Módulo de Recurrencia y Extensión de Serie (Solo en evento base/padre) -->
        @if($event->parent_id === null)
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <div class="bg-gradient-to-r from-brand-50 to-indigo-50/50 p-5 rounded-2xl border border-brand-100">
                    <div class="space-y-1">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_recurring" value="1" x-model="isRecurring"
                                   class="w-5 h-5 rounded-lg border-brand-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm font-black text-brand-950">¿Convertir en evento recurrente o agregar más sesiones/días?</span>
                        </label>
                        <p class="text-xs text-brand-800/80 ml-8">
                            Permite encadenar sesiones posteriores (Día 2, Día 3...). La sesión actual se mantendrá intacta con todas sus firmas como Sesión #1 (Base de retención).
                        </p>
                    </div>

                    <!-- Sesiones ya existentes en la serie -->
                    @if($event->sessions()->count() > 0)
                        <div class="mt-4 pt-4 border-t border-brand-200/60 space-y-2">
                            <span class="text-xs font-bold text-brand-900 uppercase tracking-wider block">Sesiones ya creadas en esta serie:</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($event->sessions as $s)
                                    <div class="bg-white p-3 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                                        <div>
                                            <span class="font-bold text-slate-900">Sesión #{{ $s->session_number }}</span>
                                            <span class="text-slate-500 block text-[11px]">{{ $s->event_date->format('d/m/Y') }} ({{ $s->attendances()->count() }} firmas)</span>
                                        </div>
                                        <a href="{{ route('admin.events.show', $s) }}" class="px-2.5 py-1 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold rounded-lg text-[11px]">
                                            Ver Sesión
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Agregar nuevas sesiones desde la edición -->
                    <div x-show="isRecurring" x-collapse class="mt-4 pt-4 border-t border-brand-200/60 space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wider">Nuevas Sesiones para Agregar</h3>
                            <button type="button" @click="addNewSession()" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1">
                                + Agregar Próxima Sesión
                            </button>
                        </div>

                        <template x-for="(session, index) in newSessions" :key="index">
                            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3 relative">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                    <span class="text-xs font-bold text-slate-900 uppercase" x-text="'Nueva Sesión (Día Siguiente)'"></span>
                                    <button type="button" @click="removeNewSession(index)" class="text-xs text-rose-600 hover:text-rose-800 font-bold">
                                        &times; Cancelar esta sesión
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Fecha <span class="text-rose-500">*</span></label>
                                        <input type="date" :name="'new_sessions[' + index + '][event_date]'" x-model="session.event_date" min="{{ $event->event_date->format('Y-m-d') }}" required
                                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Hora Inicio</label>
                                        <input type="time" :name="'new_sessions[' + index + '][start_time]'" x-model="session.start_time"
                                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Hora Fin</label>
                                        <input type="time" :name="'new_sessions[' + index + '][end_time]'" x-model="session.end_time"
                                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Ubicación</label>
                                        <input type="text" :name="'new_sessions[' + index + '][location]'" x-model="session.location"
                                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Facilitador(es)</label>
                                        <input type="text" :name="'new_sessions[' + index + '][instructor]'" x-model="session.instructor"
                                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 outline-none">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        @endif

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
                            <p class="text-[11px] text-slate-500">Se cierra automáticamente al terminar la hora fin fijada.</p>
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

    <form id="delete-form" method="POST" action="{{ route('admin.events.destroy', $event) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
function eventEditForm() {
    return {
        instructors: @json(old('instructors', $event->instructors_list)),
        newInstructor: '',
        isRecurring: @json(old('is_recurring', $event->isRecurring())),
        newSessions: [],

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

        addNewSession() {
            let baseDate = '{{ $event->event_date->format('Y-m-d') }}';
            let d = new Date(baseDate + 'T00:00:00');
            d.setDate(d.getDate() + (this.newSessions.length + 1));
            let nextDateStr = d.toISOString().split('T')[0];

            this.newSessions.push({
                event_date: nextDateStr,
                start_time: '{{ $event->start_time ? substr($event->start_time, 0, 5) : '09:00' }}',
                end_time: '{{ $event->end_time ? substr($event->end_time, 0, 5) : '13:00' }}',
                location: @json($event->location ?? ''),
                instructor: this.instructors.join(', ')
            });
        },

        removeNewSession(index) {
            this.newSessions.splice(index, 1);
        }
    };
}
</script>
@endsection
