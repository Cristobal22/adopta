<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RotateAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:rotate';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Rota los logs de auditoría con más de 6 meses, respaldándolos comprimidos en disco y purgando la base de datos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando proceso de rotación de logs de auditoría...');

        $cutoffDate = Carbon::now()->subDays(180);
        $oldLogsQuery = AuditLog::where('created_at', '<', $cutoffDate);
        $oldLogsCount = $oldLogsQuery->count();

        if ($oldLogsCount === 0) {
            $this->info('No se encontraron logs de auditoría con más de 6 meses de antigüedad para rotar.');
            return 0;
        }

        $this->info("Se encontraron {$oldLogsCount} registros para rotar. Generando respaldo...");

        // 1. Obtener los logs en lotes de 1000 para optimizar memoria
        $csvHeader = ['id', 'user_id', 'action', 'model_type', 'model_id', 'old_values', 'new_values', 'ip_address', 'created_at', 'updated_at'];
        
        $tempFile = fopen('php://temp', 'r+');
        fputcsv($tempFile, $csvHeader);

        $oldLogsQuery->chunk(1000, function ($logs) use ($tempFile) {
            foreach ($logs as $log) {
                fputcsv($tempFile, [
                    $log->id,
                    $log->user_id,
                    $log->action,
                    $log->model_type,
                    $log->model_id,
                    json_encode($log->old_values),
                    json_encode($log->new_values),
                    $log->ip_address,
                    $log->created_at,
                    $log->updated_at
                ]);
            }
        });

        // Volver al principio del archivo temporal
        rewind($tempFile);
        $csvContent = stream_get_contents($tempFile);
        fclose($tempFile);

        // 2. Comprimir el CSV usando GZIP
        $compressedContent = gzencode($csvContent, 9);
        if ($compressedContent === false) {
            $this->error('Error: Falló la compresión GZIP de los logs.');
            return 1;
        }

        // 3. Guardar el archivo en el storage privado local
        $filename = 'audit_log_backup_' . Carbon::now()->format('Y_m_d_His') . '.csv.gz';
        $relativePath = 'private/audit_backups/' . $filename;

        Storage::disk('local')->put($relativePath, $compressedContent);
        $this->info("Respaldo guardado exitosamente en: " . storage_path('app/' . $relativePath));

        // 4. Eliminar los logs de la base de datos
        $oldLogsQuery->delete();
        $this->info("Se han eliminado {$oldLogsCount} registros antiguos de la tabla 'audit_logs'.");

        // 5. Registrar el evento de rotación en los logs de auditoría (este será un registro nuevo)
        AuditService::log('audit_logs_rotated', null, null, [
            'records_rotated_count' => $oldLogsCount,
            'backup_file' => $filename,
        ]);

        return 0;
    }
}
