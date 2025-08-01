<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ContentPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos para pilares de contenido
        $permissions = [
            'content.view',
            'content.create',
            'content.edit',
            'content.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Asignar permisos al rol de administrador
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // Asignar permiso de visualización al rol de operador
        $operatorRole = Role::where('name', 'operator')->first();
        if ($operatorRole) {
            $operatorRole->givePermissionTo(['content.view']);
        }

        // Asignar permiso de visualización al rol de visualizador
        $viewerRole = Role::where('name', 'viewer')->first();
        if ($viewerRole) {
            $viewerRole->givePermissionTo(['content.view']);
        }

        $this->command->info('Permisos de pilares de contenido creados y asignados correctamente.');
    }
}
