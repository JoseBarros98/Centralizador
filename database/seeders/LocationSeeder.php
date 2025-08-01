<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Sede Central',
                'address' => 'Av. Principal #123',
                'city' => 'La Paz',
                'active' => true,
            ],
            [
                'name' => 'Campus Norte',
                'address' => 'Calle 15 #456',
                'city' => 'Santa Cruz',
                'active' => true,
            ],
            [
                'name' => 'Campus Sur',
                'address' => 'Av. Secundaria #789',
                'city' => 'Cochabamba',
                'active' => true,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
