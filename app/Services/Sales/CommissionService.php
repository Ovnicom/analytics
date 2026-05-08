<?php

namespace App\Services\Sales;

use Illuminate\Support\Facades\Cache;

class CommissionService
{
    const CACHE_TTL = 300;

    public function __construct(private OdooService $odoo) {}

    // ── Comisiones del período ────────────────────────────────

    // En CommissionService.php — reemplaza el método getByPeriod y getByYear

    private function getCargosByOrders(array $orderIds): array
    {
        if (empty($orderIds)) return ['otf' => collect(), 'mrc' => collect()];

        $cargos = $this->odoo->execute('cargo.order', 'search_read',
            [[
                ['order_id',    'in', $orderIds],
                ['charge_type', 'in', [2, 4, 8]],                          // Mensualidad, Instalación, Venta
                ['sub_state',   'in', ['draft', 'validate', 'facturado']], // excluye cancel y anulado
            ]],
            [
                'fields' => ['order_id', 'cargo_type', 'sub_amount'],
                'limit'  => 0,
            ]
        ) ?? [];

        $otf = collect($cargos)
            ->where('cargo_type', 'otf')
            ->groupBy(fn($c) => is_array($c['order_id']) ? $c['order_id'][0] : $c['order_id']);

        $mrc = collect($cargos)
            ->where('cargo_type', 'mrc')
            ->groupBy(fn($c) => is_array($c['order_id']) ? $c['order_id'][0] : $c['order_id']);

        return ['otf' => $otf, 'mrc' => $mrc];
    }

    public function getByPeriod(string $year, string $month): array
    {
        $cacheKey = "commissions:period:{$year}:{$month}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($year, $month) {

            $domain = [
                ['year_comission',            '=', $year],
                ['month_comission',           '=', $month],
                ['to_invoice_button_clicked', '=', true],
                ['state',  '!=', 'cancel'],
            ];

            $records = $this->odoo->execute('sale.order', 'search_read',
                [$domain],
                [
                    'fields' => [
                        'name', 'year_comission', 'month_comission',
                        'state', 'user_id', 'amount_total',
                        'partner_id', 'date_order',
                    ],
                    'order' => 'date_order desc',
                    'limit' => 0,
                ]
            ) ?? [];

            // Traer cargos de todos los SO de una sola llamada
            $orderIds = collect($records)->pluck('id')->all();
            $cargos   = $this->getCargosByOrders($orderIds);

            // Inyectar total_otf y total_mrc desde cargo.order
            $records = collect($records)->map(function ($r) use ($cargos) {
                $id          = $r['id'];
                $r['total_otf'] = ($cargos['otf'][$id] ?? collect())->sum('sub_amount');
                $r['total_mrc'] = ($cargos['mrc'][$id] ?? collect())->sum('sub_amount');
                return $r;
            })->all();

            return $this->process($records);
        });
    }

    public function getByYear(string $year): array
    {
        $cacheKey = "commissions:year:{$year}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($year) {

            $domain = [
                ['year_comission',            '=', $year],
                ['to_invoice_button_clicked', '=', true],
                ['state',  '!=', 'cancel'],
            ];

            $records = $this->odoo->execute('sale.order', 'search_read',
                [$domain],
                [
                    'fields' => [
                        'name', 'year_comission', 'month_comission',
                        'state', 'user_id', 'amount_total',
                        'partner_id', 'date_order',
                    ],
                    'order' => 'date_order desc',
                    'limit' => 0,
                ]
            ) ?? [];

            $orderIds = collect($records)->pluck('id')->all();
            $cargos   = $this->getCargosByOrders($orderIds);

            $records = collect($records)->map(function ($r) use ($cargos) {
                $id             = $r['id'];
                $r['total_otf'] = ($cargos['otf'][$id] ?? collect())->sum('sub_amount');
                $r['total_mrc'] = ($cargos['mrc'][$id] ?? collect())->sum('sub_amount');
                return $r;
            })->all();

            return $this->process($records);
        });
    }
    // ── Procesamiento ─────────────────────────────────────────

    private function process(array $records): array
    {
        $collection = collect($records);

        $byVendedor = $collection
            ->groupBy(fn($r) => is_array($r['user_id']) ? $r['user_id'][0] : ($r['user_id'] ?: 0))
            ->map(fn($group) => [
                'vendedor_id'   => is_array($group->first()['user_id']) ? $group->first()['user_id'][0] : '—',
                'vendedor_name' => is_array($group->first()['user_id']) ? $group->first()['user_id'][1] : '—',
                'cantidad'      => $group->count(),
                'total_otf'     => $group->sum('total_otf'),
                'total_mrc'     => $group->sum('total_mrc'),
                'total'         => $group->sum('total_otf') + $group->sum('total_mrc'),
            ])
            ->sortByDesc('total')
            ->values();

        return [
            'records'     => $records,
            'cantidad'    => $collection->count(),
            'total_otf'   => $collection->sum('total_otf'),
            'total_mrc'   => $collection->sum('total_mrc'),
            'total'       => $collection->sum('total_otf') + $collection->sum('total_mrc'),
            'by_vendedor' => $byVendedor,
        ];
    }


    // ── Caché ─────────────────────────────────────────────────

    public function clearCacheYear(string $year): void
    {
        Cache::forget("commissions:year:{$year}");
    }

    public function clearCache(string $year, string $month): void
    {
        Cache::forget("commissions:period:{$year}:{$month}");
    }
    
    public function odoo(): OdooService
    {
        return $this->odoo;
    }
}