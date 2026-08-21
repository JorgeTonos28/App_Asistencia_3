<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Panel Administrativo') - AsistenciaPro</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="h-full antialiased text-slate-800" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex flex-col lg:flex-row">
        <!-- Sidebar para Desktop -->
        <aside class="hidden lg:flex lg:flex-col lg:w-72 bg-slate-900 text-slate-300 border-r border-slate-800 flex-shrink-0">
            <div class="p-6 flex items-center gap-3 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-brand-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="font-bold text-lg text-white tracking-tight leading-none">AsistenciaPro</h1>
                    <span class="text-[11px] text-brand-400 font-medium tracking-wide uppercase">Admin Suite</span>
                </div>
            </div>

            <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
                <div class="px-3 py-2 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">Menú Principal</div>
                
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.events.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.events.*') && !request()->routeIs('admin.events.create') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Eventos y Cursos</span>
                    </div>
                </a>

                <a href="{{ route('admin.events.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.events.create') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo Evento</span>
                </a>

                <a href="{{ route('admin.participants.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.participants.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Directorio Participantes</span>
                </a>

                <div class="pt-4 px-3 py-2 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">Configuración y Usuarios</div>

                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.users.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.users.*') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>Usuarios y Roles</span>
                        </div>
                        <span class="text-[10px] font-extrabold uppercase bg-amber-400/20 text-amber-300 px-2 py-0.5 rounded-full">Admin</span>
                    </a>
                @endif

                <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.profile.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Mi Perfil</span>
                </a>
            </nav>

            <div class="p-4 border-t border-slate-800 bg-slate-950/50">
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 overflow-hidden group">
                        <div class="w-9 h-9 rounded-full {{ auth()->user()->isSuperAdmin() ? 'bg-amber-600' : 'bg-brand-700' }} text-white font-bold flex items-center justify-center text-sm flex-shrink-0 group-hover:scale-105 transition-transform">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="truncate text-left">
                            <p class="text-xs font-semibold text-white truncate group-hover:text-brand-300 transition-colors">{{ auth()->user()->name ?? 'Admin' }}</p>
                            <span class="text-[10px] font-bold {{ auth()->user()->isSuperAdmin() ? 'text-amber-400' : 'text-slate-400' }} uppercase block truncate">
                                {{ auth()->user()->isSuperAdmin() ? 'Admin General' : 'Admin Eventos' }}
                            </span>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Cerrar Sesión" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Sidebar Móvil (Drawer) -->
        <div x-show="sidebarOpen" class="fixed inset-0 z-50 lg:hidden" x-cloak>
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="sidebarOpen = false"></div>
            <div class="fixed inset-y-0 left-0 w-72 bg-slate-900 text-slate-300 p-6 flex flex-col justify-between shadow-2xl">
                <div>
                    <div class="flex items-center justify-between pb-6 border-b border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="font-bold text-white text-lg">AsistenciaPro</span>
                        </div>
                        <button @click="sidebarOpen = false" class="text-slate-400 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <nav class="mt-6 space-y-2">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white' : 'text-slate-300' }}">Dashboard</a>
                        <a href="{{ route('admin.events.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-sm {{ request()->routeIs('admin.events.*') ? 'bg-brand-600 text-white' : 'text-slate-300' }}">Eventos y Cursos</a>
                        <a href="{{ route('admin.events.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-sm {{ request()->routeIs('admin.events.create') ? 'bg-brand-600 text-white' : 'text-slate-300' }}">Nuevo Evento</a>
                        <a href="{{ route('admin.participants.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-sm {{ request()->routeIs('admin.participants.*') ? 'bg-brand-600 text-white' : 'text-slate-300' }}">Participantes</a>
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-sm {{ request()->routeIs('admin.users.*') ? 'bg-amber-600 text-white' : 'text-slate-300' }}">Usuarios y Roles</a>
                        @endif
                        <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-sm {{ request()->routeIs('admin.profile.*') ? 'bg-brand-600 text-white' : 'text-slate-300' }}">Mi Perfil</a>
                    </nav>
                </div>

                <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                    <span class="text-xs text-slate-400">{{ auth()->user()->name ?? 'Admin' }} ({{ auth()->user()->isSuperAdmin() ? 'Admin General' : 'Admin Eventos' }})</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs text-rose-400 font-semibold">Cerrar Sesión</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Barra Superior Móvil -->
            <header class="lg:hidden bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-30">
                <button @click="sidebarOpen = true" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="font-bold text-slate-900">AsistenciaPro Admin</span>
                <div class="w-8"></div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <!-- Alertas Flash -->
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-sm font-semibold">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <span class="text-sm font-semibold">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')

                <footer class="mt-12 py-4 border-t border-slate-200 text-center text-xs text-slate-400">
                    Dirección de Innovación y Análisis Estratégico de Datos - INNOVATEP
                </footer>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
