<?php

namespace App\Console\Commands;

use App\Models\Inscription;
use Illuminate\Console\Command;

class DebugHistorial extends Command
{
    protected $signature = 'debug:historial';
    protected $description = 'Debug del historial de pagos';

    public function handle()
    {
        $inscription = Inscription::first();
        
        if (!$inscription) {
            $this->error('No hay inscripciones');
            return;
        }
        
        $this->info('Inscripción ID: ' . $inscription->id);
        $this->info('Nombre: ' . $inscription->full_name);
        
        $count = $inscription->paymentHistory()->count();
        $this->info('Registros de historial: ' . $count);
        
        if ($count > 0) {
            $history = $inscription->paymentHistory()->get();
            foreach ($history as $h) {
                $this->line($h->old_status . ' → ' . $h->new_status . ' (' . $h->status_date . ')');
            }
        } else {
            $this->warn('No hay historial de pagos para esta inscripción');
        }
        
        // Verificar la tabla
        $this->info('---');
        $this->info('Verificando tabla inscription_payment_history:');
        $allRecords = DB::table('inscription_payment_history')->count();
        $this->info('Total de registros en tabla: ' . $allRecords);
    }
}
