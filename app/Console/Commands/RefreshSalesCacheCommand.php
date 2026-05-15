<?php

namespace App\Console\Commands;

use App\Services\Sales\CommissionService;
use App\Services\Sales\OdooService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RefreshSalesCacheCommand extends Command
{
    protected $signature   = 'sales:refresh-cache';
    protected $description = 'Limpia el caché de comisiones y Odoo para que se regenere fresco';

    public function handle(CommissionService $commissions, OdooService $odoo): int
    {
        $now  = Carbon::now();
        $prev = $now->copy()->subMonth();

        // Comisiones
        $commissions->clearCache((string) $now->year,  (string) $now->month);
        $commissions->clearCache((string) $prev->year, (string) $prev->month);
        $commissions->clearCacheYear((string) $now->year);

        // Odoo KPIs, pipeline, ejecutivas, etc.
        $odoo->clearCache();

        $this->info('Caché de ventas limpiado — ' . $now->toDateTimeString());

        return self::SUCCESS;
    }
}
