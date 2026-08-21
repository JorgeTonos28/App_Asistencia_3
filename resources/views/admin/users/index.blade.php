@extends('layouts.admin')

@section('title', 'Gestión de Administradores y Usuarios')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Administradores del Sistema</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Gestión de cuentas y roles de acceso administrativo.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-600/20 transition-all inline-flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            <span>Nuevo Administrador</span>
        </a>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="bg-white rounded-3xl p-4 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <div class="relative flex-1 sm:w-72">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o correo..."
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 outline-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <select name="role" class="px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                <option value="">Todos los Roles</option>
                <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Admin General</option>
                <option value="event_admin" {{ request('role') === 'event_admin' ? 'selected' : '' }}>Admin de Eventos</option>
            </select>

            <button type="submit" class="px-3.5 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700">Filtrar</button>
            @if(request('search') || request('role'))
                <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200">Limpiar</a>
            @endif
        </form>

        <span class="text-xs text-slate-500 font-medium">Total: <strong>{{ $users->total() }}</strong> usuario(s)</span>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-4">Usuario</th>
                        <th class="py-3.5 px-4">Correo Electrónico</th>
                        <th class="py-3.5 px-4">Rol Asignado</th>
                        <th class="py-3.5 px-4 text-center">Estado</th>
                        <th class="py-3.5 px-4">Fecha de Registro</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 text-white font-bold flex items-center justify-center text-xs flex-shrink-0">
                                        {{ substr($u->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 block">{{ $u->name }}</span>
                                        @if($u->id === auth()->id())
                                            <span class="text-[10px] text-brand-600 font-bold">(Tú)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">
                                {{ $u->email }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($u->isSuperAdmin())
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-200">
                                        Admin General
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-blue-100 text-blue-800 border border-blue-200">
                                        Admin de Eventos
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($u->is_active)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $u->created_at->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.users.edit', $u) }}" class="p-1.5 text-slate-600 hover:text-brand-600 hover:bg-slate-100 rounded-lg transition-colors" title="Editar Usuario">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    @if($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('¿Estás seguro de eliminar este administrador?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Eliminar Usuario">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                No se encontraron administradores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
