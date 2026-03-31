<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario administrador
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@sistema.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Crear usuario marketing
        $marketing = User::create([
            'name' => 'Marketing',
            'email' => 'marketing@sistema.com',
            'password' => Hash::make('password'),
        ]);
        $marketing->assignRole('marketing');

        // Crear usuario academic
        $academic = User::create([
            'name' => 'Academico',
            'email' => 'academic@sistema.com',
            'password' => Hash::make('password'),
        ]);
        $academic->assignRole('academic');

        // Crear usuario design
        $design = User::create([
            'name' => 'Diseñador',
            'email' => 'design@sistema.com',
            'password' => Hash::make('password'),
        ]);
        $design->assignRole('design');

        // Crear usuario accountant
        $accountant = User::create([
            'name' => 'Contador',
            'email' => 'accountant@sistema.com',
            'password' => Hash::make('password'),
        ]);
        $accountant->assignRole('accountant');
    }
}
