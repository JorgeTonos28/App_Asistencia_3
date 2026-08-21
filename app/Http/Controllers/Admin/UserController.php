<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Valida que el usuario actual sea Super Admin antes de procesar cualquier acción.
     */
    protected function ensureSuperAdmin()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Acceso denegado: solo el Administrador General puede gestionar usuarios y roles del sistema.');
        }
    }

    /**
     * Listado de administradores y usuarios del sistema.
     */
    public function index(Request $request)
    {
        $this->ensureSuperAdmin();

        $query = User::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Formulario de creación de nuevo administrador.
     */
    public function create()
    {
        $this->ensureSuperAdmin();

        return view('admin.users.create');
    }

    /**
     * Guarda un nuevo administrador en el sistema.
     */
    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:superadmin,event_admin'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'role.required' => 'Debes seleccionar un rol para el usuario.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.users.index')->with('success', '¡Nuevo administrador creado satisfactoriamente!');
    }

    /**
     * Formulario de edición de un usuario.
     */
    public function edit(User $user)
    {
        $this->ensureSuperAdmin();

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Actualiza los datos y rol de un usuario.
     */
    public function update(Request $request, User $user)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'in:superadmin,event_admin'],
            'password' => ['nullable', 'confirmed', Password::min(6)],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya está en uso.',
            'role.required' => 'El rol es obligatorio.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
        ]);

        // Evitar que el superadmin actual se desactive o se quite el rol a sí mismo
        if ($user->id === auth()->id()) {
            $validated['role'] = 'superadmin';
            $validated['is_active'] = true;
        } else {
            $validated['is_active'] = $request->boolean('is_active', true);
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $validated['is_active'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', '¡Datos del administrador actualizados con éxito!');
    }

    /**
     * Elimina un usuario administrador.
     */
    public function destroy(User $user)
    {
        $this->ensureSuperAdmin();

        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta de administrador.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario administrador eliminado correctamente.');
    }
}
