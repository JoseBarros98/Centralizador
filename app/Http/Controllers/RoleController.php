<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:role.view'])->only(['index', 'show']);
        $this->middleware(['permission:role.create'])->only(['create', 'store']);
        $this->middleware(['permission:role.edit'])->only(['edit', 'update']);
        $this->middleware(['permission:role.delete'])->only(['destroy']);
    }

    public function index()
    {
        $roles = Role::withCount('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $groupedPermissions = $this->getGroupedPermissions();
        return view('roles.create', compact('groupedPermissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('roles.index')
            ->with('success', "Rol \"{$role->name}\" creado correctamente.");
    }

    public function show(Role $role)
    {
        $groupedPermissions = $this->getGroupedPermissions($role->permissions->pluck('name')->toArray());
        $usersCount = $role->users()->count();
        return view('roles.show', compact('role', 'groupedPermissions', 'usersCount'));
    }

    public function edit(Role $role)
    {
        $groupedPermissions  = $this->getGroupedPermissions($role->permissions->pluck('name')->toArray());
        $rolePermissions     = $role->permissions->pluck('name')->toArray();
        return view('roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        $role->name = $request->name;
        $role->save();
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('roles.index')
            ->with('success', "Rol \"{$role->name}\" actualizado correctamente.");
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->route('roles.index')
                ->with('error', 'El rol de administrador no puede ser eliminado.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', "No se puede eliminar el rol \"{$role->name}\" porque tiene {$role->users()->count()} usuario(s) asignado(s).");
        }

        $name = $role->name;
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', "Rol \"{$name}\" eliminado correctamente.");
    }

    private function getGroupedPermissions(array $selected = []): array
    {
        $moduleLabels = [
            'dashboard'          => 'Dashboards',
            'inscription'        => 'Inscripciones',
            'inscriptions'       => 'Inscripciones (Sync)',
            'content'            => 'Contenido',
            'content_pillar'     => 'Pilar de Contenido',
            'type_of_art'        => 'Tipo de Arte',
            'program'            => 'Programas',
            'calendar'           => 'Calendario',
            'teacher'            => 'Docentes',
            'user'               => 'Usuarios',
            'role'               => 'Roles',
            'marketing'          => 'Marketing',
            'payment_request'    => 'Solicitudes de Pago',
            'graduation_cite'    => 'Titulación',
            'program_allocation' => 'Asignación de Programas',
            'marketing_contact'  => 'Contactos Marketing',
            'system'             => 'Sistema',
        ];

        $actionLabels = [
            'view'                     => 'Ver',
            'create'                   => 'Crear',
            'edit'                     => 'Editar',
            'delete'                   => 'Eliminar',
            'toggle_active'            => 'Activar/Desactivar',
            'manage_files'             => 'Gestionar Archivos',
            'manage_teams'             => 'Gestionar Equipos',
            'manage_goals'             => 'Gestionar Metas',
            'view_reports'             => 'Ver Reportes',
            'view_attendance'          => 'Ver Asistencia',
            'manage_attendance'        => 'Gestionar Asistencia',
            'export_attendance'        => 'Exportar Asistencia',
            'request_teacher_payments' => 'Solicitar Pagos Docentes',
            'view_logs'                => 'Ver Logs',
            'sync'                     => 'Sincronizar',
            'marketing'                => 'Dashboard Marketing',
            'academic'                 => 'Dashboard Académico',
            'accounting'               => 'Dashboard Contabilidad',
            'design'                   => 'Dashboard Diseño',
            'view_own'                 => 'Ver (solo propios)',
            'edit_own'                 => 'Editar (solo propios)',
            'delete_own'               => 'Eliminar (solo propios)',
            'view_team'                => 'Ver equipo',
        ];

        $grouped = [];

        foreach (Permission::orderBy('name')->get() as $permission) {
            $parts  = explode('.', $permission->name, 2);
            $module = $parts[0];
            $action = $parts[1] ?? $permission->name;

            if (!isset($grouped[$module])) {
                $grouped[$module] = [
                    'label'       => $moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module)),
                    'permissions' => [],
                ];
            }

            $grouped[$module]['permissions'][] = [
                'name'         => $permission->name,
                'action_label' => $actionLabels[$action] ?? ucfirst(str_replace('_', ' ', $action)),
                'checked'      => in_array($permission->name, $selected),
            ];
        }

        uksort($grouped, fn($a, $b) => strcmp(
            $moduleLabels[$a] ?? $a,
            $moduleLabels[$b] ?? $b
        ));

        return $grouped;
    }
}
