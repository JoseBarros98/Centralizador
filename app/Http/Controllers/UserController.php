<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:user.view'])->only(['index', 'show']);
        $this->middleware(['permission:user.create'])->only(['create', 'store']);
        $this->middleware(['permission:user.edit'])->only(['edit', 'update']);
        $this->middleware(['permission:user.delete'])->only(['destroy', 'toggleActive']);
    }

    public function index(Request $request)
    {
        $query = User::with('roles');
        
        // Filtrar por estado si se proporciona
        if ($request->has('active') && $request->active != '') {
            $query->where('active', $request->active);
        }
        
        // Aplicar filtro de búsqueda si existe
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'active' => $request->has('active') ? true : false,
        ]);

        // Obtener los roles por ID y asignarlos por nombre
        $roleNames = Role::whereIn('id', $request->roles)->pluck('name')->toArray();
        $user->syncRoles($roleNames);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        
        return view('users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->active = $request->has('active');
        
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();
        
        // Obtener los roles por ID y asignarlos por nombre
        $roleNames = Role::whereIn('id', $request->roles)->pluck('name')->toArray();
        $user->syncRoles($roleNames);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        // En lugar de eliminar, desactivamos al usuario
        return $this->toggleActive($user, false);
    }
    
    public function toggleActive(User $user, $active = null)
    {
        // Evitar desactivar al usuario administrador principal
        if ($active === false && $user->hasRole('admin') && User::role('admin')->where('active', true)->count() <= 1) {
            return redirect()->route('users.index')
                ->with('error', 'No se puede desactivar el único usuario administrador activo.');
        }
        
        $newStatus = $active !== null ? $active : !$user->active;
        $user->active = $newStatus;
        $user->save();
        
        $message = $user->active ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.';
        
        return redirect()->route('users.index')
            ->with('success', $message);
    }
}
