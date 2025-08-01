<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AssignContentPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-content-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna los permisos necesarios para el módulo de pilares de contenido';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Asignando permisos para el módulo de pilares de contenido...');
        
        $this->call('db:seed', [
            '--class' => 'ContentPermissionSeeder',
        ]);
        
        $this->info('¡Permisos asignados correctamente!');
    }
}
