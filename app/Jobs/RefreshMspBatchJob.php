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

            Log::info('Batch MSP actualizado', ['batch_id' => $batch->id]);
        } finally {
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }
}
