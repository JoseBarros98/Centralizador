<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $marketingRole = Role::firstOrCreate(['name' => 'marketing']);
        $academicRole = Role::firstOrCreate(['name' => 'academic']);
        $designRole = Role::firstOrCreate(['name' => 'design']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);

        // Lista de permisos que deberían existir
        $expectedPermissions = [
            'dashboard.view',
            'inscription.view', 'inscription.create', 'inscription.edit', 'inscription.delete',
            'inscriptions.sync',

            'content.view', 'content.create', 'content.edit', 'content.delete', 'content.toggle_active', 'content.manage_files',

            'content_pillar.view', 'content_pillar.create', 'content_pillar.edit', 'content_pillar.delete', 'content_pillar.toggle_active', 'content_pillar.manage_files',

            'type_of_art.view', 'type_of_art.create', 'type_of_art.edit', 'type_of_art.delete', 'type_of_art.toggle_active',

            'program.view', 'program.create', 'program.edit', 'program.delete',
            'program.view_attendance', 'program.manage_attendance', 'program.export_attendance',
            'program.request_teacher_payments',

            'user.view', 'user.create', 'user.edit', 'user.delete',

            'marketing.view', 'marketing.create', 'marketing.edit', 'marketing.delete',
            'marketing.manage_teams', 'marketing.manage_goals', 'marketing.view_reports',

            'payment_request.view', 'payment_request.create', 'payment_request.edit', 'payment_request.delete',
            
            'graduation_cite.view', 'graduation_cite.create', 'graduation_cite.edit', 'graduation_cite.delete',

            'program_allocation.view', 'program_allocation.create', 'program_allocation.edit', 'program_allocation.delete',
            
            'system.view_logs'
        ];

        // Solo crear permisos que no existan
        foreach ($expectedPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Limpiar la caché antes de asignar permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Asignar todos los permisos al rol de administrador
        $adminRole->syncPermissions(Permission::all());
        
        // Marketing
        $marketingRole->syncPermissions([
            'inscription.view', 'inscription.create', 'inscription.edit', 'inscription.delete',
            'program.view',
            'content.view', 'content.create', 'content.edit', 'content.delete',
            'marketing.view', 'marketing.create', 'marketing.edit', 'marketing.delete',
            'marketing.manage_teams', 'marketing.manage_goals', 'marketing.view_reports',
            'dashboard.view'
        ]);
        
        // Designer
        $designRole->syncPermissions([
            'content_pillar.view', 'content_pillar.create', 'content_pillar.edit', 'content_pillar.delete', 'content_pillar.toggle_active', 'content_pillar.manage_files',
            'type_of_art.view', 'type_of_art.create', 'type_of_art.edit', 'type_of_art.delete', 'type_of_art.toggle_active',
            'content.view', 'content.create', 'content.edit', 'content.delete', 'content.toggle_active', 'content.manage_files',
            'dashboard.view'
        ]);

        // Academic
        $academicPermissions = collect($expectedPermissions)->diff([
            'inscription.create', 'inscription.delete',
            'content_pillar.view', 'content_pillar.create', 'content_pillar.edit', 'content_pillar.delete', 'content_pillar.toggle_active', 'content_pillar.manage_files',
            'type_of_art.view', 'type_of_art.create', 'type_of_art.edit', 'type_of_art.delete', 'type_of_art.toggle_active',
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'program_allocation.view', 'program_allocation.create', 'program_allocation.edit', 'program_allocation.delete',
            'system.view_logs'
        ])->toArray();
        
        $academicRole->syncPermissions($academicPermissions);
        
        // Accountant
        $accountantRole->syncPermissions([
            'dashboard.view',
            'program.view',
            'program.request_teacher_payments',
            'payment_request.view', 'payment_request.create', 'payment_request.edit', 'payment_request.delete',
            'graduation_cite.view', 'graduation_cite.create', 'graduation_cite.edit', 'graduation_cite.delete',
            'program_allocation.view', 'program_allocation.create', 'program_allocation.edit', 'program_allocation.delete'
        ]);
    }
}