<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ExternalSystemUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario especial para sincronización con sistema externo
        User::firstOrCreate(
            ['email' => 'sistema.externo@centtest.local'],
            [
                'name' => 'Sistema Externo',
                'email' => 'sistema.externo@centtest.local',
                'password' => Hash::make('sistema-externo-' . bin2hex(random_bytes(16))), // Password aleatorio muy seguro
                'email_verified_at' => now(),
            ]
        );
    }
}
