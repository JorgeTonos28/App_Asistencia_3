<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyección de Asistencia en Vivo - {{ $event->title }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .print-card { border: 2px solid #cbd5e1 !important; box-shadow: none !important; color: black !important; background: white !important; }
            .print-text-dark { color: #0f172a !important; }
            .print-text-muted { color: #64748b !important; }
        }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between p-4 sm:p-6 lg:p-8 antialiased selection:bg-brand-500 selection:text-white"
      x-data="projectionApp('{{ route('attendance.live_feed', ['code' => $event->access_code]) }}')" x-init="init()">

    <!-- Barra de Controles (Los botones de navegación administrativa solo aparecen si el Administrador está logueado) -->
    <header class="no-print w-full max-w-7xl mx-auto flex items-center justify-between pb-4 mb-4 border-b border-slate-800/80">
        <div class="flex items-center gap-3">
            @auth
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.events.show', $event) }}" class="text-xs font-bold text-slate-300 hover:text-white flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-all" title="Regresar a la gestión del evento">
                        <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Detalle Evento</span>
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-slate-300 hover:text-white flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-all" title="Ir al panel principal">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Panel Admin</span>
                    </a>
                </div>
            @endauth
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Proyección en Vivo</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="toggleFullScreen()" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-slate-700 transition-all inline-flex items-center gap-1.5" title="Alternar Pantalla Completa">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Pantalla Completa</span>
            </button>
            <button onclick="window.print()" class="px-3.5 py-1.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir QR</span>
            </button>
        </div>
    </header>

    <!-- Contenido Principal: QR + Información + Flujo en Vivo -->
    <main class="w-full max-w-7xl mx-auto my-auto py-2">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Columna Izquierda: Tarjeta de Proyección y QR (5 Columnas) -->
            <div class="lg:col-span-6 xl:col-span-5 space-y-5">
                <div class="print-card bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 text-center shadow-2xl backdrop-blur-xl space-y-5 relative overflow-hidden">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-brand-500/20 text-brand-300 border border-brand-500/30 text-xs font-bold uppercase tracking-wider">
                        Registro de Asistencia Digital
                    </div>

                    <div class="space-y-2">
                        <h1 class="print-text-dark text-2xl sm:text-3xl font-black tracking-tight leading-tight text-white">{{ $event->title }}</h1>
                        <p class="print-text-muted text-xs sm:text-sm text-slate-300">
                            {{ $event->event_date->format('d/m/Y') }}
                            @if($event->instructor) · {{ $event->instructor }} @endif
                            @if($event->location) · {{ $event->location }} @endif
                        </p>
                    </div>

                    <!-- Código QR Gigante -->
                    <div class="py-2">
                        <div class="p-5 bg-white rounded-3xl inline-block shadow-2xl border-4 border-white">
                            {!! QrCode::size(240)->style('round')->generate($registrationUrl) !!}
                        </div>
                    </div>

                    <div class="space-y-1.5 max-w-xs mx-auto">
                        <div class="flex items-center justify-center gap-2 text-emerald-400 font-bold text-xs sm:text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span>Apunta con tu cámara para firmar</span>
                        </div>
                        <p class="print-text-muted text-[11px] text-slate-400">
                            Ingresa tu código de empleado o cédula para validar tu asistencia.
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-800">
                        <span class="print-text-muted text-[10px] text-slate-500 uppercase tracking-wider block mb-0.5">Enlace web directo:</span>
                        <a href="{{ $registrationUrl }}" target="_blank" class="text-xs font-mono font-bold text-brand-400 hover:underline break-all">
                            {{ $registrationUrl }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Métricas y Flujo de Asistencias en Tiempo Real (7 Columnas) -->
            <div class="lg:col-span-6 xl:col-span-7 space-y-5 no-print">
                <!-- Contador de Asistentes en Vivo -->
                <div class="bg-gradient-to-r from-slate-900 to-slate-800 border border-slate-800 rounded-3xl p-6 shadow-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Asistentes Confirmados</span>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-4xl sm:text-5xl font-black text-emerald-400 tracking-tight" x-text="count">0</span>
                            <span class="text-xs text-slate-400 font-medium">participantes</span>
                        </div>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>

                <!-- Feed de Asistentes en Vivo -->
                <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <span>Registros Recientes</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        </h3>
                        <span class="text-[11px] text-slate-400">Actualizado al instante</span>
                    </div>

                    <div class="space-y-3 max-h-[460px] overflow-y-auto p-1">
                        <template x-if="attendances.length === 0">
                            <div class="py-12 text-center text-slate-500 text-xs space-y-2">
                                <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center mx-auto text-slate-400">
                                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p>Esperando a los primeros participantes...</p>
                            </div>
                        </template>

                        <template x-for="(item, index) in attendances" :key="item.id">
                            <div class="p-3.5 rounded-2xl flex items-center justify-between gap-3 transition-all duration-300"
                                 :class="index === 0 ? 'bg-slate-800/95 border-2 border-emerald-500 shadow-lg shadow-emerald-500/10' : 'bg-slate-800/80 border border-slate-700/80'">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl text-white font-black flex items-center justify-center text-sm shadow-md flex-shrink-0"
                                         :class="index === 0 ? 'bg-gradient-to-tr from-emerald-600 to-teal-500' : 'bg-gradient-to-tr from-brand-600 to-indigo-600'"
                                         x-text="item.full_name.substring(0, 1)">
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs sm:text-sm font-bold text-white leading-tight" x-text="item.full_name"></h4>
                                            <template x-if="index === 0">
                                                <span class="text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2 py-0.2 rounded-full uppercase">Último</span>
                                            </template>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-slate-400 mt-0.5 font-mono">
                                            <template x-if="item.employee_code && item.employee_code !== 'N/A'">
                                                <span x-text="'Cód: ' + item.employee_code"></span>
                                            </template>
                                            <template x-if="item.document_number && item.document_number !== 'N/A'">
                                                <span x-text="(item.employee_code && item.employee_code !== 'N/A' ? '• ' : '') + item.document_number"></span>
                                            </template>
                                            <template x-if="item.department && item.department !== 'N/A' && item.department !== 'INFOTEP'">
                                                <span x-text="'• ' + item.department"></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 flex-shrink-0">
                                    <!-- Miniatura de la Firma Digital -->
                                    <template x-if="item.signature_url">
                                        <div class="p-1 bg-white rounded-lg shadow-sm">
                                            <img :src="item.signature_url" alt="Firma" class="h-6 max-w-[70px] object-contain">
                                        </div>
                                    </template>

                                    <div class="text-right">
                                        <span class="text-xs font-bold text-slate-300 block" x-text="item.check_in_time"></span>
                                        <span class="text-[10px] text-slate-500" x-text="item.check_in_diff"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer Institucional -->
    <footer class="no-print text-center text-xs text-slate-500 py-3">
        Dirección de Innovación y Análisis Estratégico de Datos - INNOVATEP
    </footer>

    <script>
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    alert(`Error al activar pantalla completa: ${err.message}`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        function projectionApp(feedUrl) {
            return {
                feedUrl: feedUrl,
                count: {{ $event->attendances()->count() }},
                attendances: [],
                pollingInterval: null,

                init() {
                    this.fetchData();
                    this.pollingInterval = setInterval(() => {
                        this.fetchData();
                    }, 3000);
                },

                fetchData() {
                    fetch(this.feedUrl)
                        .then(res => res.json())
                        .then(data => {
                            this.count = data.count;
                            this.attendances = data.attendances;
                        })
                        .catch(err => console.error('Error fetching projection feed:', err));
                }
            }
        }
    </script>
</body>
</html>
