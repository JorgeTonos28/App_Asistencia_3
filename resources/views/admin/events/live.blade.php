@extends('layouts.admin')

@section('title', 'Monitoreo en Vivo - ' . $event->title)

@section('content')
<div class="space-y-6" x-data="liveAttendance('{{ route('admin.events.live_feed', $event) }}')" x-init="startPolling()">
    <!-- Header del Monitor en Vivo -->
    <div class="bg-slate-900 text-white p-6 sm:p-8 rounded-3xl shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 border border-slate-800">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    EN VIVO Y TRANSMITIENDO
                </span>
                <span class="text-xs font-mono text-slate-400 bg-slate-800 px-2.5 py-1 rounded-lg border border-slate-700">
                    CÓDIGO: {{ $event->access_code }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">{{ $event->title }}</h1>
            <p class="text-xs text-slate-400 flex items-center gap-4">
                <span>📅 {{ $event->event_date->format('d/m/Y') }}</span>
                @if($event->instructor) <span>👨‍🏫 {{ $event->instructor }}</span> @endif
                @if($event->location) <span>📍 {{ $event->location }}</span> @endif
            </p>
        </div>

        <!-- Contador Gigante en Vivo -->
        <div class="flex items-center gap-4 bg-slate-800/80 p-5 rounded-2xl border border-slate-700/80">
            <div class="text-right">
                <span class="text-4xl sm:text-5xl font-black text-emerald-400 tracking-tight" x-text="count">0</span>
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Asistentes Registrados</span>
            </div>
            <div class="h-12 w-[1px] bg-slate-700"></div>
            <div class="flex flex-col gap-2">
                <a href="{{ route('attendance.qr', ['code' => $event->access_code]) }}" target="_blank" class="p-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5" title="Proyectar QR en Pantalla Completa (Público)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Proyectar QR</span>
                </a>
                <a href="{{ route('admin.events.show', $event) }}" class="p-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-xl text-xs font-semibold text-center">
                    Ver Detalle
                </a>
            </div>
        </div>
    </div>

    <!-- Contenido en Dos Columnas: Feed en Vivo y Mini QR -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Feed de Asistentes en Vivo (2 Columnas) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-slate-900">Flujo de Registros en Tiempo Real</h2>
                    <span class="text-xs bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full font-bold" x-text="attendances.length"></span>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Actualización automática (3s)</span>
                </div>
            </div>

            <!-- Lista de Asistencias que entran en vivo -->
            <div class="space-y-3 min-h-[300px]">
                <template x-if="attendances.length === 0">
                    <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm">
                        <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4 animate-bounce">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Esperando participantes...</h3>
                        <p class="text-xs text-slate-500 mt-1">Los registros aparecerán aquí automáticamente al firmar.</p>
                    </div>
                </template>

                <template x-for="(item, index) in attendances" :key="item.id">
                    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between gap-4 transition-all duration-300 hover:border-brand-500"
                         :class="{ 'ring-2 ring-emerald-500 bg-emerald-50/40': index === 0 }">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-600 to-indigo-600 text-white font-black flex items-center justify-center text-sm shadow-md shadow-brand-500/20 flex-shrink-0"
                                 x-text="item.full_name.substring(0, 1)">
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-slate-900" x-text="item.full_name"></h4>
                                    <span class="text-[11px] font-mono font-bold text-slate-500 px-2 py-0.5 rounded bg-slate-100" x-text="item.document_number"></span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    <span x-text="'📞 ' + (item.phone || 'S/N')"></span>
                                    <span class="mx-1.5 text-slate-300">•</span>
                                    <span x-text="'🏢 ' + (item.department || 'General')"></span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 flex-shrink-0">
                            <!-- Thumbnail de Firma -->
                            <template x-if="item.signature_url">
                                <div class="p-1 bg-slate-50 border border-slate-200 rounded-lg">
                                    <img :src="item.signature_url" alt="Firma" class="h-7 max-w-[80px] object-contain">
                                </div>
                            </template>

                            <div class="text-right">
                                <span class="text-xs font-bold text-slate-900 block" x-text="item.check_in_time"></span>
                                <span class="text-[10px] text-slate-400" x-text="item.check_in_diff"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Columna Lateral: Tarjeta con QR para escanear en pantalla -->
        <div class="space-y-4">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm text-center space-y-4">
                <span class="text-xs font-bold text-brand-600 bg-brand-50 px-3 py-1 rounded-full uppercase tracking-wider">
                    Escanea para Registrarte
                </span>
                
                <div class="p-4 bg-white border-2 border-slate-900 rounded-2xl inline-block shadow-lg shadow-slate-200">
                    {!! QrCode::size(200)->style('round')->generate(route('attendance.form', ['code' => $event->access_code])) !!}
                </div>

                <div class="space-y-1">
                    <p class="text-xs font-bold text-slate-800">Apunta con la cámara de tu teléfono móvil</p>
                    <p class="text-[11px] text-slate-500">O ingresa manualmente al enlace:</p>
                    <a href="{{ route('attendance.form', ['code' => $event->access_code]) }}" target="_blank" class="text-xs text-brand-600 hover:underline font-mono block break-all font-semibold">
                        {{ route('attendance.form', ['code' => $event->access_code]) }}
                    </a>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <a href="{{ route('attendance.qr', ['code' => $event->access_code]) }}" target="_blank" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                        <span>Pantalla Completa para Auditorio</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function liveAttendance(feedUrl) {
        return {
            feedUrl: feedUrl,
            count: {{ $event->attendances()->count() }},
            attendances: [],
            pollingInterval: null,

            startPolling() {
                this.fetchData();
                this.pollingInterval = setInterval(() => {
                    this.fetchData();
                }, 3000);
            },

            fetchData() {
                fetch(this.feedUrl)
                    .then(response => response.json())
                    .then(data => {
                        this.count = data.count;
                        this.attendances = data.attendances;
                    })
                    .catch(err => console.error('Error fetching live attendances:', err));
            }
        }
    }
</script>
@endsection
