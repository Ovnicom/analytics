<?php

namespace App\Jobs;

use App\Imports\MspReportsImport;
use App\Models\MspReport;
use App\Models\MspUploadBatch;
use App\Services\SharePointService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class RefreshMspBatchJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;
    public int $tries = 1;
    public int $uniqueFor = 1200;

    public function __construct(public int $batchId)
    {
    }

    public function uniqueId(): string
    {
        return (string) $this->batchId;
    }

    public function handle(SharePointService $sharePoint): void
    {
        $batch = MspUploadBatch::findOrFail($this->batchId);
        $tempPath = null;

        $batch->update([
            'refresh_status' => 'processing',
            'refresh_message' => 'Descargando e importando el archivo de SharePoint.',
            'refresh_started_at' => now(),
            'refresh_finished_at' => null,
        ]);

        try {
            $tempPath = $sharePoint->downloadFileById($batch->sharepoint_item_id, $batch->filename);

            DB::transaction(function () use ($batch, $tempPath) {
                MspReport::where('batch_id', $batch->id)->delete();
                Excel::import(new MspReportsImport($batch->periodo, $batch->id), $tempPath);

                $batch->update([
                    'total_registros' => MspReport::where('batch_id', $batch->id)->count(),
                    'clientes_unicos' => MspReport::where('batch_id', $batch->id)
                        ->distinct('customer_name')
                        ->count(),
                ]);
            });

            $batch->update([
                'refresh_status' => 'completed',
                'refresh_message' => 'Actualización completada correctamente.',
                'refresh_finished_at' => now(),
            ]);

            Log::info('Batch MSP actualizado', ['batch_id' => $batch->id]);
        } catch (\Throwable $e) {
            $batch->update([
                'refresh_status' => 'failed',
                'refresh_message' => 'No se pudo completar la actualización.',
                'refresh_finished_at' => now(),
            ]);

            Log::error('Error al actualizar batch MSP', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }
}
