<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        $adminRole = Role::create(['name' => 'admin']);
        $marketingRole = Role::create(['name' => 'marketing']);
        $academicRole = Role::create(['name' => 'academic']);
        $designRole = Role::create(['name' => 'design']);
        $operatorRole = Role::create(['name' => 'operator']);
        $viewerRole = Role::create(['name' => 'viewer']);

        // Permisos del Sistema (actualizados según los constructores)
        $permissions = [
            // Dashboard
            'dashboard.view',
            
            // Inscription
            'inscription.view', 'inscription.create', 'inscription.edit', 'inscription.delete',
            
            // ArtRequest (content.* según el constructor)
            'content.view', 'content.create', 'content.edit', 'content.delete', 'content.toggle_active', 'content.manage_files',
            
            // ContentPillar
            'content_pillar.view', 'content_pillar.create', 'content_pillar.edit', 'content_pillar.delete', 'content_pillar.toggle_active', 'content_pillar.manage_files',
            
            // TypeOfArt
            'type_of_art.view', 'type_of_art.create', 'type_of_art.edit', 'type_of_art.delete', 'type_of_art.toggle_active',
            
            // Program
            'program.view', 'program.create', 'program.edit', 'program.delete',
            
            // Module
            'program.view', // Nota: Según constructores, módulos usan permisos de program
            
            // ModuleClass
            'program.view', 'program.create', 'program.edit', 'program.delete',
            
            // Attendance
            'program.view_attendance', 'program.manage_attendance', 'program.export_attendance',
            
            // Calendar
            'program.view',
            
            // User
            'user.view', 'user.create', 'user.edit', 'user.delete',
            
            // DocumentFollowup
            'inscription.view', 'inscription.edit',
            
            // GradeFollowup
            'program.view', 'program.create',
            
            // Document
            'inscription.view', 'inscription.create', 'inscription.edit', 'inscription.delete',
            
            // Receipt
            'inscription.view', 'inscription.edit', 'inscription.delete'
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Asignar todos los permisos al rol de administrador
        $adminRole->givePermissionTo(Permission::all());
        
        // Marketing
        $marketingRole->givePermissionTo([
            'inscription.view', 'inscription.create', 'inscription.edit', 'inscription.delete',
            'program.view',
            'content.view', 'content.create', 'content.edit', 'content.delete',
            'dashboard.view'
        ]);
        
        // Designer
        $designRole->givePermissionTo([
            'content_pillar.view', 'content_pillar.create', 'content_pillar.edit', 'content_pillar.delete', 'content_pillar.toggle_active', 'content_pillar.manage_files',
            'type_of_art.view', 'type_of_art.create', 'type_of_art.edit', 'type_of_art.delete', 'type_of_art.toggle_active',
            'content.view', 'content.create', 'content.edit', 'content.delete', 'content.toggle_active', 'content.manage_files',
            'dashboard.view'
        ]);

        // Academic
        $academicPermissions = collect($permissions)->diff([
            'inscription.create', 'inscription.delete',
            'content_pillar.view', 'content_pillar.create', 'content_pillar.edit', 'content_pillar.delete', 'content_pillar.toggle_active', 'content_pillar.manage_files',
            'type_of_art.view', 'type_of_art.create', 'type_of_art.edit', 'type_of_art.delete', 'type_of_art.toggle_active',
            'user.view', 'user.create', 'user.edit', 'user.delete',
        ])->toArray();
        
        $academicRole->givePermissionTo($academicPermissions);
        
        // Operator
        $operatorRole->givePermissionTo([
            'inscription.view', 'inscription.create', 'inscription.edit',
            'program.view',
            'dashboard.view'
        ]);
        
        // Viewer
        $viewerRole->givePermissionTo([
            'inscription.view',
            'program.view',
            'dashboard.view'
        ]);
    }
}