<?php

namespace App\Console\Commands;

use App\Models\Adoption;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAdoptionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adoption:send-reminders';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios automáticos de diarios de adopción en días clave (Día 3, Día 15, Mes 3, 1 Año).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Escaneando adopciones finalizadas para envío de recordatorios...');

        $adoptions = Adoption::with(['adopter', 'pet'])
            ->where('status', 'finalizado')
            ->get();

        $remindedCount = 0;

        foreach ($adoptions as $adoption) {
            $adoptionDate = Carbon::parse($adoption->updated_at);
            $daysSinceAdoption = $adoptionDate->diffInDays(Carbon::today());

            $milestone = null;

            switch ($daysSinceAdoption) {
                case 3:
                    $milestone = 'Día 3 (Adaptación Inicial)';
                    break;
                case 15:
                    $milestone = 'Día 15 (Primera Quincena)';
                    break;
                case 90:
                    $milestone = 'Mes 3 (Acomodo y Rutinas)';
                    break;
                case 365:
                    $milestone = '1 Año (Aniversario de Familia)';
                    break;
            }

            if ($milestone) {
                $this->info("Enviando recordatorio para Adopción #{$adoption->id}: Mascota {$adoption->pet->name} al Adoptante {$adoption->adopter->name} - Hito: {$milestone}");
                
                // Simulación de envío de correo/notificación transaccional
                Log::info("Adoption Diary Reminder Sent: Adopter {$adoption->adopter->email} for Pet {$adoption->pet->name} ({$milestone})");
                
                $remindedCount++;
            }
        }

        $this->info("Proceso completado. Se enviaron {$remindedCount} recordatorios.");
    }
}
