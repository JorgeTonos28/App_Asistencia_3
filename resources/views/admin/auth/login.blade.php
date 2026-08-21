@extends('layouts.guest')

@section('title', 'Acceso al Panel Administrativo')

@section('content')
<div class="max-w-md mx-auto my-6 sm:my-10">
    <div class="bg-white rounded-3xl p-8 sm:p-10 shadow-xl shadow-slate-200/60 border border-slate-200/80">
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-700 to-indigo-600 flex items-center justify-center text-white mx-auto mb-4 shadow-lg shadow-brand-500/25">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Iniciar Sesión</h2>
            <p class="text-xs text-slate-500 mt-1.5">Ingresa tus credenciales para acceder al panel administrativo</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
                @foreach($errors->all() as $error)
                    <p class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $error }}
                    </p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" autocomplete="off" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Correo Electrónico</label>
                <div class="relative">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="off"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm transition-all bg-slate-50/50 focus:bg-white"
                           placeholder="tu.correo@ejemplo.com">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                    </div>
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Contraseña</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required autocomplete="new-password"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm transition-all bg-slate-50/50 focus:bg-white"
                           placeholder="••••••••">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span>Recordar sesión</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-700 hover:to-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-brand-500/25 transition-all transform active:scale-[0.99] text-sm flex items-center justify-center gap-2">
                <span>Ingresar al Sistema</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </form>
    </div>
</div>
@endsection
