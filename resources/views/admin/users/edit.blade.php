@extends('layouts.admin')

@section('title', 'Editar Administrador - ' . $user->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-slate-500 hover:text-brand-600 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver a usuarios
        </a>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Editar Administrador</h1>
        <p class="text-xs text-slate-500 mt-0.5">Modifica los datos, rol o restablece la contraseña de <strong>{{ $user->name }}</strong>.</p>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
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
            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nombre Completo <span class="text-rose-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Correo Electrónico <span class="text-rose-500">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
            </div>

            <!-- Selección de Rol -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Rol y Nivel de Acceso <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all {{ old('role', $user->role) === 'event_admin' ? 'border-brand-600 bg-brand-50/40' : 'border-slate-200 hover:border-slate-300' }}">
                        <div class="flex items-center justify-between mb-1">
                            <input type="radio" name="role" value="event_admin" {{ old('role', $user->role) === 'event_admin' ? 'checked' : '' }}
                                   {{ $user->id === auth()->id() ? 'disabled' : '' }} class="text-brand-600 focus:ring-brand-500">
                            <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">Eventos</span>
                        </div>
                        <span class="text-xs font-bold text-slate-900">Admin de Eventos</span>
                        <p class="text-[11px] text-slate-500 mt-1">Crea y gestiona eventos, asistencias y reportes. No administra otros usuarios.</p>
                    </label>

                    <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all {{ old('role', $user->role) === 'superadmin' ? 'border-amber-500 bg-amber-50/40' : 'border-slate-200 hover:border-slate-300' }}">
                        <div class="flex items-center justify-between mb-1">
                            <input type="radio" name="role" value="superadmin" {{ old('role', $user->role) === 'superadmin' ? 'checked' : '' }}
                                   {{ $user->id === auth()->id() ? 'disabled' : '' }} class="text-amber-600 focus:ring-amber-500">
                            <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Total</span>
                        </div>
                        <span class="text-xs font-bold text-slate-900">Admin General</span>
                        <p class="text-[11px] text-slate-500 mt-1">Control total del sistema y gestión de todos los administradores.</p>
                    </label>
                </div>
                @if($user->id === auth()->id())
                    <p class="text-[11px] text-slate-400 mt-1.5">No puedes cambiar tu propio rol de Administrador General.</p>
                @endif
            </div>

            <!-- Restablecer Contraseña (Opcional) -->
            <div class="pt-4 border-t border-slate-100 space-y-3">
                <span class="text-xs font-bold text-slate-800 block">Cambiar Contraseña (Opcional)</span>
                <p class="text-[11px] text-slate-500">Deja estos campos vacíos si no deseas cambiar la contraseña del usuario.</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-medium text-slate-700 mb-1">Nueva Contraseña</label>
                        <input type="password" id="password" name="password" placeholder="••••••••"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-medium text-slate-700 mb-1">Confirmar Nueva Contraseña</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                    </div>
                </div>
            </div>

            @if($user->id !== auth()->id())
                <div class="pt-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-xs font-bold text-slate-700">Usuario Activo (Permitir inicio de sesión)</span>
                    </label>
                </div>
            @endif
        </div>

        <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-600/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Guardar Cambios</span>
            </button>
        </div>
    </form>
</div>
@endsection
