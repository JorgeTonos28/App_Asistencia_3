@extends('layouts.admin')

@section('title', 'Directorio de Participantes')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Directorio de Participantes</h1>
            <p class="text-xs text-slate-500 mt-1">Base de datos de personas registradas para autocompletado en futuros eventos</p>
        </div>
    </div>

    <!-- Búsqueda -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.participants.index') }}" class="flex gap-3">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por cédula, código, nombre, teléfono o departamento..." 
                       class="w-full pl-10 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 outline-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold">
                Buscar
            </button>
            @if(request('search'))
                <a href="{{ route('admin.participants.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold flex items-center justify-center">
                    Limpiar
                </a>
            @endif
        </form>
    </div>

    <!-- Tabla de Participantes -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-4 text-center w-12">#</th>
                        <th class="py-3.5 px-4">Cédula / Código</th>
                        <th class="py-3.5 px-4">Nombre y Apellido</th>
                        <th class="py-3.5 px-4">Teléfono / Extensión</th>
                        <th class="py-3.5 px-4">Departamento / Área</th>
                        <th class="py-3.5 px-4 text-center">Eventos Asistidos</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($participants as $index => $participant)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 text-center font-bold text-slate-400">
                                {{ $participants->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                {{ $participant->document_number }}
                            </td>
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.participants.show', $participant) }}" class="font-bold text-slate-900 hover:text-brand-600">
                                    {{ $participant->full_name }}
                                </a>
                                @if($participant->email)
                                    <span class="block text-[11px] text-slate-400">{{ $participant->email }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                {{ $participant->phone ?? 'N/A' }}
                            </td>
                            <td class="py-3 px-4">
                                {{ $participant->institution_department ?? 'N/A' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700">
                                    {{ $participant->attendances_count }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.participants.show', $participant) }}" class="p-1.5 text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Ver Historial">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.participants.edit', $participant) }}" class="p-1.5 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                No se encontraron participantes registrados en el catálogo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($participants->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $participants->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
