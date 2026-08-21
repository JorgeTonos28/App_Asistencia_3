@extends('layouts.guest')

@section('title', 'Registro de Asistencia - ' . $event->title)

@push('styles')
<style>
    .signature-container {
        position: relative;
        touch-action: none;
        user-select: none;
        -webkit-user-select: none;
        -webkit-touch-callout: none;
    }
    canvas#signature-pad {
        touch-action: none;
        user-select: none;
        -webkit-user-select: none;
        cursor: crosshair;
    }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Encabezado del Evento -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-brand-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-brand-500/10 blur-3xl pointer-events-none"></div>
        <div class="relative z-10 space-y-3">
            <div class="inline-flex flex-wrap items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-brand-200 border border-white/10">
                <span>Hoja de Registro Digital</span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Código: {{ $event->access_code }}</span>
                @if($event->isRecurring())
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-300"></span>
                    <span class="text-white font-bold">{{ $event->series_session_label }}</span>
                @endif
            </div>

            <h1 class="text-2xl sm:text-3xl font-black tracking-tight leading-snug">{{ $event->title }}</h1>

            @if($event->description)
                <p class="text-xs sm:text-sm text-slate-300">{{ $event->description }}</p>
            @endif

            <div class="flex flex-wrap items-center gap-y-2 gap-x-5 pt-3 border-t border-white/10 text-xs text-slate-300">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="font-medium">{{ $event->event_date->format('d/m/Y') }}</span>
                    @if($event->start_time)
                        <span>({{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }})</span>
                    @endif
                </div>

                @if($event->formatted_instructors && $event->formatted_instructors !== 'No asignado')
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Facilitador(es): <strong class="text-white">{{ $event->formatted_instructors }}</strong></span>
                    </div>
                @endif

                @if($event->location)
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ $event->location }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(!$event->is_registration_open)
        <div class="bg-white rounded-3xl p-8 border border-slate-200 text-center space-y-4 shadow-sm">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h2 class="text-xl font-bold text-slate-900">{{ $event->registration_status_info['message'] }}</h2>
            <p class="text-sm text-slate-500 max-w-md mx-auto">
                @if($event->registration_status_info['reason'] === 'expired_schedule')
                    El horario establecido para este evento ha concluido. El registro digital de asistencia se ha cerrado automáticamente.
                @else
                    El registro de firmas y asistencia para este evento no está disponible en este momento.
                @endif
            </p>
        </div>
    @else
        <!-- Formulario de Registro con Firma Digital -->
        <form id="attendance-form" method="POST" action="{{ route('attendance.register', ['code' => $event->access_code]) }}" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
            @csrf

            <!-- Barra Superior del Formulario: Botón Limpiar Todo -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Formulario de Asistencia</span>
                <button type="button" id="btn-reset-form" class="px-3 py-1.5 text-xs font-bold text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-xl border border-slate-200 hover:border-rose-200 transition-all inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Limpiar Campos</span>
                </button>
            </div>

            <!-- Aviso de Autocompletado (oculto por defecto) -->
            <div id="autocomplete-alert" class="hidden p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 transition-all duration-300">
                <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-xs font-bold text-emerald-950">¡Participante / Empleado Encontrado!</h4>
                    <p class="text-xs text-emerald-800 mt-0.5">Hemos cargado tus datos automáticamente. Por favor verifica tu información y realiza tu firma al final.</p>
                </div>
                <button type="button" onclick="document.getElementById('autocomplete-alert').classList.add('hidden')" class="text-emerald-700 hover:text-emerald-900 text-xs font-bold">&times;</button>
            </div>

            <!-- Paso 1: Identificación con autocompletado en tiempo real -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-brand-600 text-white flex items-center justify-center text-[10px] font-extrabold">1</span>
                        Identificación
                    </h2>
                    <span id="lookup-spinner" class="hidden text-xs text-brand-600 font-semibold flex items-center gap-1.5">
                        <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Buscando datos...
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Código de Empleado (Siempre Obligatorio) -->
                    <div>
                        <label for="employee_code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Código de Empleado <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="employee_code" name="employee_code" value="{{ old('employee_code') }}" required autofocus
                                   placeholder="Ej: 5445"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-base font-mono font-bold text-slate-900 tracking-wide">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Ingresa tu código para buscar tus datos automáticamente.</p>
                    </div>

                    <!-- Cédula (Obligatoria u Opcional según evento) -->
                    <div>
                        <label for="document_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Cédula @if($event->require_document)<span class="text-rose-500">*</span>@else<span class="text-slate-400 font-normal">(Opcional)</span>@endif
                        </label>
                        <div class="relative">
                            <input type="text" id="document_number" name="document_number" value="{{ old('document_number') }}"
                                   @if($event->require_document) required @endif
                                   placeholder="Ej: 402-2022923-7"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-base font-mono font-bold text-slate-900 tracking-wide">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">También puedes buscar por cédula.</p>
                    </div>
                </div>
            </div>

            <!-- Paso 2: Nombres y Apellidos en Plural -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-brand-600 text-white flex items-center justify-center text-[10px] font-extrabold">2</span>
                    Datos Personales
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">NOMBRES <span class="text-rose-500">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required placeholder="Tus nombres"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                    </div>
                    <div>
                        <label for="last_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">APELLIDOS <span class="text-rose-500">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required placeholder="Tus apellidos"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. Teléfono o Extensión <span class="text-slate-400 font-normal">(Opcional)</span></label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Ej: 809-555-0101 ó Ext. 104"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                    </div>
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Correo Electrónico <span class="text-slate-400 font-normal">(Opcional)</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="tu.correo@infotep.gob.do"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                    </div>
                </div>

                @if($event->department_mode !== 'hidden')
                    <div>
                        <label for="institution_department" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Departamento / Área @if($event->department_mode === 'required')<span class="text-rose-500">*</span>@else<span class="text-slate-400 font-normal">(Opcional)</span>@endif
                        </label>
                        <input type="text" id="institution_department" name="institution_department" value="{{ old('institution_department') }}"
                               @if($event->department_mode === 'required') required @endif
                               placeholder="Ej: Innovación / Recursos Humanos / INFOTEP"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                    </div>
                @endif
            </div>

            <!-- Paso 3: Firma Digital -->
            <div class="space-y-3 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-brand-600 text-white flex items-center justify-center text-[10px] font-extrabold">3</span>
                        Firma Digital <span class="text-rose-500">*</span>
                    </h2>
                    <button type="button" id="btn-clear-signature" class="text-xs font-bold text-rose-600 hover:text-rose-700 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Limpiar Firma</span>
                    </button>
                </div>

                <p class="text-xs text-slate-500">Dibuja tu firma con tu dedo o cursor en el recuadro inferior:</p>

                <!-- Contenedor del Canvas de Firma -->
                <div class="signature-container bg-slate-50 border-2 border-dashed border-slate-300 rounded-2xl p-1 relative overflow-hidden focus-within:border-brand-500 transition-colors">
                    <canvas id="signature-pad" class="w-full h-44 sm:h-48 bg-white rounded-xl touch-none block" style="touch-action: none;"></canvas>
                    <div id="signature-placeholder" class="absolute inset-0 flex items-center justify-center pointer-events-none text-slate-400 text-xs font-medium gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Firma aquí</span>
                    </div>
                </div>

                <!-- Campo oculto para enviar la firma en base64 -->
                <input type="hidden" name="signature" id="signature-input">
            </div>

            <!-- Botones de Acción -->
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center gap-3">
                <button type="button" id="btn-reset-form-bottom" class="w-full sm:w-auto px-5 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Limpiar Todo</span>
                </button>
                <button type="submit" id="btn-submit" class="flex-1 w-full py-4 px-6 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-700 hover:to-indigo-700 text-white font-black text-sm rounded-2xl shadow-xl shadow-brand-500/25 transition-all transform active:scale-[0.99] flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Confirmar y Registrar Mi Asistencia</span>
                </button>
            </div>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. CANVAS DE FIRMA DIGITAL ---
        const canvas = document.getElementById('signature-pad');
        const signatureContainer = document.querySelector('.signature-container');
        const signaturePlaceholder = document.getElementById('signature-placeholder');
        const signatureInput = document.getElementById('signature-input');
        const btnClear = document.getElementById('btn-clear-signature');
        const form = document.getElementById('attendance-form');

        let signaturePad = null;
        let resizeTimer = null;

        // Función para retirar el foco y ocultar el teclado virtual al tocar la zona de firma
        function dismissActiveInput() {
            if (document.activeElement && typeof document.activeElement.blur === 'function' && document.activeElement !== document.body) {
                document.activeElement.blur();
            }
        }

        if (canvas) {
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const rect = canvas.getBoundingClientRect();
                const newWidth = Math.round(rect.width * ratio);
                const newHeight = Math.round(rect.height * ratio);

                // Si las dimensiones no han cambiado, no recalcular para evitar parpadeos
                if (canvas.width === newWidth && canvas.height === newHeight) {
                    return;
                }

                // Guardar trazo previo para no perderlo al abrir o cerrar el teclado del móvil
                let savedData = null;
                if (signaturePad && !signaturePad.isEmpty()) {
                    savedData = signaturePad.toData();
                }

                canvas.width = newWidth;
                canvas.height = newHeight;
                const ctx = canvas.getContext("2d");
                ctx.scale(ratio, ratio);

                if (signaturePad) {
                    signaturePad.clear();
                    if (savedData && savedData.length > 0) {
                        signaturePad.fromData(savedData);
                        if (signaturePlaceholder) signaturePlaceholder.style.display = 'none';
                    }
                }
            }

            // Trazo fino, suave y natural
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(15, 23, 42)',
                minWidth: 0.8,
                maxWidth: 2.2,
                velocityFilterWeight: 0.7,
                throttle: 16
            });

            resizeCanvas();

            window.addEventListener("resize", function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(resizeCanvas, 150);
            });

            // Al interactuar con el lienzo de firma, quitar el foco de cualquier input abierto
            if (signatureContainer) {
                signatureContainer.addEventListener('touchstart', dismissActiveInput, { passive: true });
                signatureContainer.addEventListener('pointerdown', dismissActiveInput, { passive: true });
                signatureContainer.addEventListener('mousedown', dismissActiveInput, { passive: true });
            }

            signaturePad.addEventListener("beginStroke", () => {
                dismissActiveInput();
                if (signaturePlaceholder) signaturePlaceholder.style.display = 'none';
            });

            btnClear.addEventListener('click', function () {
                signaturePad.clear();
                signatureInput.value = '';
                if (signaturePlaceholder) signaturePlaceholder.style.display = 'flex';
            });
        }

        // --- 2. AUTOCOMPLETADO INTELIGENTE POR CÓDIGO O CÉDULA ---
        const codeInput = document.getElementById('employee_code');
        const docInput = document.getElementById('document_number');
        const firstNameInput = document.getElementById('first_name');
        const lastNameInput = document.getElementById('last_name');
        const phoneInput = document.getElementById('phone');
        const emailInput = document.getElementById('email');
        const deptInput = document.getElementById('institution_department');
        const alertBox = document.getElementById('autocomplete-alert');
        const spinner = document.getElementById('lookup-spinner');
        const btnResetTop = document.getElementById('btn-reset-form');
        const btnResetBottom = document.getElementById('btn-reset-form-bottom');

        let lookupTimeout = null;
        let lastLookupTerm = '';

        function performLookup(term) {
            const cleanTerm = term ? term.trim() : '';
            if (cleanTerm.length < 2) return;
            if (cleanTerm === lastLookupTerm) return;

            if (spinner) spinner.classList.remove('hidden');

            fetch(`{{ route('participants.lookup') }}?term=${encodeURIComponent(cleanTerm)}`)
                .then(res => res.json())
                .then(data => {
                    if (spinner) spinner.classList.add('hidden');
                    if (data.found && data.participant) {
                        lastLookupTerm = cleanTerm;
                        const p = data.participant;
                        if (codeInput && p.employee_code) codeInput.value = p.employee_code;
                        if (docInput && p.document_number) docInput.value = p.document_number;
                        if (firstNameInput && p.first_name) firstNameInput.value = p.first_name;
                        if (lastNameInput && p.last_name) lastNameInput.value = p.last_name;
                        if (phoneInput && p.phone) phoneInput.value = p.phone;
                        if (emailInput && p.email) emailInput.value = p.email;
                        if (deptInput && p.institution_department) deptInput.value = p.institution_department;

                        if (alertBox) {
                            alertBox.classList.remove('hidden');
                        }
                    }
                })
                .catch(err => {
                    if (spinner) spinner.classList.add('hidden');
                    console.error('Error during participant lookup:', err);
                });
        }

        if (codeInput) {
            codeInput.addEventListener('input', function () {
                clearTimeout(lookupTimeout);
                lookupTimeout = setTimeout(() => performLookup(codeInput.value), 450);
            });
            codeInput.addEventListener('change', function () {
                performLookup(codeInput.value);
            });
        }

        if (docInput) {
            docInput.addEventListener('input', function () {
                clearTimeout(lookupTimeout);
                lookupTimeout = setTimeout(() => performLookup(docInput.value), 450);
            });
            docInput.addEventListener('change', function () {
                performLookup(docInput.value);
            });
        }

        // --- 3. FUNCIÓN PARA LIMPIAR TODO EL FORMULARIO ---
        function resetEntireForm() {
            lastLookupTerm = '';
            if (codeInput) codeInput.value = '';
            if (docInput) docInput.value = '';
            if (firstNameInput) firstNameInput.value = '';
            if (lastNameInput) lastNameInput.value = '';
            if (phoneInput) phoneInput.value = '';
            if (emailInput) emailInput.value = '';
            if (deptInput) deptInput.value = '';
            if (signatureInput) signatureInput.value = '';

            if (signaturePad) {
                signaturePad.clear();
            }
            if (signaturePlaceholder) {
                signaturePlaceholder.style.display = 'flex';
            }
            if (alertBox) {
                alertBox.classList.add('hidden');
            }
            if (codeInput) {
                codeInput.focus();
            }
        }

        if (btnResetTop) {
            btnResetTop.addEventListener('click', resetEntireForm);
        }
        if (btnResetBottom) {
            btnResetBottom.addEventListener('click', resetEntireForm);
        }

        // --- 4. VALIDACIÓN AL ENVIAR FORMULARIO ---
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!signaturePad || signaturePad.isEmpty()) {
                    e.preventDefault();
                    alert('Por favor realiza tu firma digital antes de enviar tu registro de asistencia.');
                    canvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }

                // Extraemos la firma en formato Data URL PNG
                signatureInput.value = signaturePad.toDataURL('image/png');
            });
        }
    });
</script>
@endpush
