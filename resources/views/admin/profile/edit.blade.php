@extends('layouts.admin')

@section('title', 'Mi Perfil y Configuración')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Mi Perfil y Cuenta</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Administra tu información de acceso, datos personales y seguridad.</p>
    </div>

    <!-- Resumen de Cuenta y Rol -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-brand-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div class="flex items-center gap-4 relative z-10">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-500 to-indigo-600 text-white font-black text-2xl flex items-center justify-center shadow-lg shadow-brand-500/25 flex-shrink-0">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider {{ $user->isSuperAdmin() ? 'bg-amber-400/20 text-amber-300 border border-amber-400/30' : 'bg-brand-400/20 text-brand-300 border border-brand-400/30' }}">
                        {{ $user->role_label }}
                    </span>
                </div>
                <p class="text-xs text-slate-300 font-mono mt-0.5">{{ $user->email }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Miembro desde {{ $user->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        @if($user->isSuperAdmin())
            <div class="relative z-10">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold border border-white/10 transition-all inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Gestionar Usuarios</span>
                </a>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- 1. Formulario de Datos Personales -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
            <div>
                <h3 class="text-base font-bold text-slate-900">Información Personal</h3>
                <p class="text-xs text-slate-500 mt-0.5">Actualiza tu nombre visible y correo de inicio de sesión.</p>
            </div>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nombre Completo <span class="text-rose-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Correo Electrónico <span class="text-rose-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
                    @error('email')
                        <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Rol en el Sistema</label>
                    <input type="text" value="{{ $user->role_label }}" disabled
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-sm font-semibold cursor-not-allowed">
                    <p class="text-[11px] text-slate-400 mt-1">El rol solo puede ser modificado por el Administrador General.</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-600/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Guardar Información</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. Formulario de Cambio de Contraseña -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
            <div>
                <h3 class="text-base font-bold text-slate-900">Seguridad y Contraseña</h3>
                <p class="text-xs text-slate-500 mt-0.5">Asegura tu cuenta actualizando periódicamente tu clave.</p>
            </div>

            <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Contraseña Actual <span class="text-rose-500">*</span></label>
                    <input type="password" id="current_password" name="current_password" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm"
                           placeholder="••••••••">
                    @error('current_password')
                        <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nueva Contraseña <span class="text-rose-500">*</span></label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm"
                           placeholder="Mínimo 6 caracteres">
                    @error('password')
                        <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Confirmar Nueva Contraseña <span class="text-rose-500">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm"
                           placeholder="Repite la nueva contraseña">
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Actualizar Contraseña</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
