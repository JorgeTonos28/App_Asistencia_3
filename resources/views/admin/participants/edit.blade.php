@extends('layouts.admin')

@section('title', 'Editar Participante')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.participants.index') }}" class="text-xs font-bold text-slate-500 hover:text-brand-600 inline-flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al directorio
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Editar Información del Participante</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.participants.update', $participant) }}" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-5">
        @csrf
        @method('PUT')

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
                @foreach($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div>
            <label for="document_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Cédula o Código de Empleado <span class="text-rose-500">*</span></label>
            <input type="text" id="document_number" name="document_number" value="{{ old('document_number', $participant->document_number) }}" required
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm font-mono font-bold">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="first_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nombre <span class="text-rose-500">*</span></label>
                <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $participant->first_name) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
            </div>
            <div>
                <label for="last_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Apellido <span class="text-rose-500">*</span></label>
                <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $participant->last_name) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Teléfono o Extensión <span class="text-rose-500">*</span></label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $participant->phone) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
            </div>
            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email', $participant->email) }}"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
            </div>
        </div>

        <div>
            <label for="institution_department" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Departamento / Área / Institución</label>
            <input type="text" id="institution_department" name="institution_department" value="{{ old('institution_department', $participant->institution_department) }}"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none text-sm">
        </div>

        <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
            <button type="button" onclick="if(confirm('¿Eliminar participante y su historial?')) document.getElementById('delete-participant').submit();" class="text-xs font-bold text-rose-600 hover:text-rose-700">
                Eliminar del Catálogo
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.participants.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-600/20 transition-all">
                    Guardar Cambios
                </button>
            </div>
        </div>
    </form>

    <form id="delete-participant" action="{{ route('admin.participants.destroy', $participant) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
