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
            'dashboard.marketing', 'dashboard.academic', 'dashboard.accounting', 'dashboard.design',

            'inscription.view', 'inscription.view_own', 'inscription.create', 'inscription.edit', 'inscription.edit_own', 'inscription.delete', 'inscription.delete_own',
            'inscriptions.sync',

            'content.view', 'content.view_own', 'content.create', 'content.edit', 'content.edit_own', 'content.delete', 'content.delete_own', 'content.toggle_active', 'content.manage_files',

            'content_pillar.view', 'content_pillar.create', 'content_pillar.edit', 'content_pillar.edit_own', 'content_pillar.delete', 'content_pillar.delete_own', 'content_pillar.toggle_active', 'content_pillar.manage_files',

            'type_of_art.view', 'type_of_art.create', 'type_of_art.edit', 'type_of_art.edit_own', 'type_of_art.delete', 'type_of_art.delete_own', 'type_of_art.toggle_active',

            'program.view', 'program.create', 'program.edit', 'program.edit_own', 'program.delete', 'program.delete_own',
            'program.view_attendance', 'program.manage_attendance', 'program.export_attendance',
            'program.request_teacher_payments',

            'calendar.view',

            'teacher.view', 'teacher.create', 'teacher.edit', 'teacher.edit_own', 'teacher.delete', 'teacher.delete_own',

            'user.view', 'user.create', 'user.edit', 'user.delete',

            'marketing.view', 'marketing.create', 'marketing.edit', 'marketing.delete',
            'marketing.manage_teams', 'marketing.manage_goals', 'marketing.view_reports',

            'payment_request.view', 'payment_request.create', 'payment_request.edit', 'payment_request.edit_own', 'payment_request.delete', 'payment_request.delete_own',

            'graduation_cite.view', 'graduation_cite.create', 'graduation_cite.edit', 'graduation_cite.edit_own', 'graduation_cite.delete', 'graduation_cite.delete_own',

            'program_allocation.view', 'program_allocation.create', 'program_allocation.edit', 'program_allocation.edit_own', 'program_allocation.delete', 'program_allocation.delete_own',

            'role.view', 'role.create', 'role.edit', 'role.delete',

            'marketing_contact.view', 'marketing_contact.view_own', 'marketing_contact.view_team',
            'marketing_contact.create',
            'marketing_contact.edit', 'marketing_contact.edit_own',
            'marketing_contact.delete', 'marketing_contact.delete_own',

            'system.view_logs',
            'system.backup',
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
            'dashboard.marketing',
            'inscription.view_own', 'inscription.create', 'inscription.edit_own', 'inscription.delete_own',
            'program.view',
            'content.view_own', 'content.create', 'content.edit_own', 'content.delete_own',
            'marketing.view', 'marketing.create', 'marketing.edit', 'marketing.delete',
            'marketing.manage_teams', 'marketing.manage_goals', 'marketing.view_reports',
            'marketing_contact.view_own', 'marketing_contact.view_team', 'marketing_contact.create',
            'marketing_contact.edit_own', 'marketing_contact.delete_own',
        ]);

        // Designer
        $designRole->syncPermissions([
            'dashboard.design',
            'content_pillar.view', 'content_pillar.create', 'content_pillar.edit', 'content_pillar.delete', 'content_pillar.toggle_active', 'content_pillar.manage_files',
            'type_of_art.view', 'type_of_art.create', 'type_of_art.edit', 'type_of_art.delete', 'type_of_art.toggle_active',
            'content.view', 'content.create', 'content.toggle_active', 'content.manage_files',
        ]);

        // Academic
        $academicRole->syncPermissions([
            'dashboard.academic',
            'inscription.view', 'inscription.edit',
            'inscriptions.sync',
            'content.view', 'content.create', 'content.edit_own', 'content.delete_own',
            'program.view', 'program.create', 'program.edit', 'program.delete',
            'program.view_attendance', 'program.manage_attendance', 'program.export_attendance',
            'program.request_teacher_payments',
            'calendar.view',
            'teacher.view', 'teacher.create', 'teacher.edit', 'teacher.delete',
            'marketing.view', 'marketing.create', 'marketing.edit', 'marketing.delete',
            'marketing.manage_teams', 'marketing.manage_goals', 'marketing.view_reports',
        ]);

        // Accountant
        $accountantRole->syncPermissions([
            'dashboard.accounting',
            'program.view',
            'program.request_teacher_payments',
            'payment_request.view', 'payment_request.create', 'payment_request.edit', 'payment_request.delete',
            'graduation_cite.view', 'graduation_cite.create', 'graduation_cite.edit', 'graduation_cite.delete',
            'program_allocation.view', 'program_allocation.create', 'program_allocation.edit', 'program_allocation.delete',
        ]);
    }
}