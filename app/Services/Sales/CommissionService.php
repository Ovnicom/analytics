<?php

namespace App\Services\Sales;

use Illuminate\Support\Facades\Cache;

class CommissionService
{
    const CACHE_TTL = 300;

    const MONTH_LABELS = [
        1 => 'Enero',    2 => 'Febrero',   3 => 'Marzo',     4 => 'Abril',
        5 => 'Mayo',     6 => 'Junio',     7 => 'Julio',     8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    public function __construct(private OdooService $odoo) {}

    // ── Cargos ────────────────────────────────────────────────

    private function getCargosByOrders(array $orderIds): array
    {
        if (empty($orderIds)) return ['otf' => collect(), 'mrc' => collect()];

        $cargos = $this->odoo->execute('cargo.order', 'search_read',
            [[
                ['order_id',    'in', $orderIds],
                ['charge_type', 'in', [2, 4, 8]],
                ['sub_state',   'not in', ['new', 'anulado']],
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

    // ── Adendas ───────────────────────────────────────────────

    private function getAdendas(array $vendedorIds, string $year, string $month): array
    {
        if (empty($vendedorIds)) return [];

        $adendas = $this->odoo->execute('cargo.adenda', 'search_read',
            [[
                ['partner_comercial_user_id_dynamic', 'in', $vendedorIds],
                ['year_comission',                    '=', $year],
                ['month_comission',                   '=', $month],
            ]],
            [
                'fields' => ['partner_comercial_user_id_dynamic', 'diff_amount'],
                'limit'  => 0,
            ]
        ) ?? [];

        return collect($adendas)
            ->groupBy(fn($a) => is_array($a['partner_comercial_user_id_dynamic'])
                ? $a['partner_comercial_user_id_dynamic'][0]
                : $a['partner_comercial_user_id_dynamic']
            )
            ->map(fn($group) => $group->sum('diff_amount'))
            ->all();
    }

    // Trae todas las adendas del año de una sola llamada y las indexa por [mes][vendedor_id]
    private function getAdendasForYear(array $vendedorIds, string $year): array
    {
        if (empty($vendedorIds)) return [];

        $adendas = $this->odoo->execute('cargo.adenda', 'search_read',
            [[
                ['partner_comercial_user_id_dynamic', 'in', $vendedorIds],
                ['year_comission',                    '=', $year],
            ]],
            [
                'fields' => ['partner_comercial_user_id_dynamic', 'month_comission', 'diff_amount'],
                'limit'  => 0,
            ]
        ) ?? [];

        $result = [];
        foreach ($adendas as $a) {
            $vid   = is_array($a['partner_comercial_user_id_dynamic'])
                ? $a['partner_comercial_user_id_dynamic'][0]
                : $a['partner_comercial_user_id_dynamic'];
            $month = (int) $a['month_comission'];
            $result[$month][$vid] = ($result[$month][$vid] ?? 0) + $a['diff_amount'];
        }
        return $result;
    }

    // ── Por período ───────────────────────────────────────────

    public function getByPeriod(string $year, string $month): array
    {
        $cacheKey = "commissions:period:{$year}:{$month}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($year, $month) {

            $domain = [
                ['year_comission',            '=', $year],
                ['month_comission',           '=', $month],
                ['to_invoice_button_clicked', '=', true],
                ['state',                     '!=', 'cancel'],
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

            $orderIds    = collect($records)->pluck('id')->all();
            $vendedorIds = collect($records)
                ->map(fn($r) => is_array($r['user_id']) ? $r['user_id'][0] : $r['user_id'])
                ->filter()->unique()->values()->all();

            $cargos  = $this->getCargosByOrders($orderIds);
            $adendas = $this->getAdendas($vendedorIds, $year, $month);

            $records = collect($records)->map(function ($r) use ($cargos, $adendas) {
                $id         = $r['id'];
                $vendedorId = is_array($r['user_id']) ? $r['user_id'][0] : $r['user_id'];

                $r['total_otf']    = ($cargos['otf'][$id] ?? collect())->sum('sub_amount');
                $r['total_mrc']    = ($cargos['mrc'][$id] ?? collect())->sum('sub_amount');
                $r['total_adenda'] = $adendas[$vendedorId] ?? 0;
                return $r;
            })->all();

            return $this->process($records, $adendas);
        });
    }

    // ── Por año ───────────────────────────────────────────────

    public function getByYear(string $year): array
    {
        $cacheKey = "commissions:year:{$year}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($year) {

            $domain = [
                ['year_comission',            '=', $year],
                ['to_invoice_button_clicked', '=', true],
                ['state',                     '!=', 'cancel'],
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

            $orderIds    = collect($records)->pluck('id')->all();
            $vendedorIds = collect($records)
                ->map(fn($r) => is_array($r['user_id']) ? $r['user_id'][0] : $r['user_id'])
                ->filter()->unique()->values()->all();

            $adendas = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthAdendas = $this->getAdendas($vendedorIds, $year, (string) $m);
                foreach ($monthAdendas as $vid => $diff) {
                    $adendas[$vid] = ($adendas[$vid] ?? 0) + $diff;
                }
            }

            $cargos = $this->getCargosByOrders($orderIds);

            $records = collect($records)->map(function ($r) use ($cargos, $adendas) {
                $id         = $r['id'];
                $vendedorId = is_array($r['user_id']) ? $r['user_id'][0] : $r['user_id'];

                $r['total_otf']    = ($cargos['otf'][$id] ?? collect())->sum('sub_amount');
                $r['total_mrc']    = ($cargos['mrc'][$id] ?? collect())->sum('sub_amount');
                $r['total_adenda'] = $adendas[$vendedorId] ?? 0;
                return $r;
            })->all();

            return $this->process($records, $adendas);
        });
    }

    // ── Procesamiento ─────────────────────────────────────────

    private function process(array $records, array $adendas = []): array
    {
        $collection = collect($records);

        $byVendedor = $collection
            ->groupBy(fn($r) => is_array($r['user_id']) ? $r['user_id'][0] : ($r['user_id'] ?: 0))
            ->map(function ($group) use ($adendas) {
                $uid          = $group->first()['user_id'];
                $vendedorId   = is_array($uid) ? $uid[0] : ($uid ?: 0);
                $vendedorName = is_array($uid) ? $uid[1] : '—';
                $totalOtf     = $group->sum('total_otf');
                $totalMrc     = $group->sum('total_mrc') + ($adendas[$vendedorId] ?? 0); // ← adenda va en MRC

                return [
                    'vendedor_id'   => $vendedorId,
                    'vendedor_name' => $vendedorName,
                    'cantidad'      => $group->count(),
                    'total_otf'     => $totalOtf,
                    'total_mrc'     => $totalMrc,
                    'total'         => $totalOtf + $totalMrc,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $totalOtf = $byVendedor->sum('total_otf');
        $totalMrc = $byVendedor->sum('total_mrc');

        return [
            'records'     => $records,
            'cantidad'    => $collection->count(),
            'total_otf'   => $totalOtf,
            'total_mrc'   => $totalMrc,
            'total'       => $totalOtf + $totalMrc,
            'by_vendedor' => $byVendedor,
        ];
    }

    // ── Por vendedor / mes ────────────────────────────────────

    public function getForVendedorMonth(int $vendedorId, string $year, string $month): ?array
    {
        $cacheKey = "commissions:vendedor:{$vendedorId}:period:{$year}:{$month}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($vendedorId, $year, $month) {
            $records = $this->odoo->execute('sale.order', 'search_read',
                [[
                    ['user_id',                   '=', $vendedorId],
                    ['year_comission',            '=', $year],
                    ['month_comission',           '=', $month],
                    ['to_invoice_button_clicked', '=', true],
                    ['state',                     '!=', 'cancel'],
                ]],
                ['fields' => ['name', 'user_id', 'amount_total', 'date_order'], 'limit' => 0]
            ) ?? [];

            $vendedorName = $this->resolveVendedorName($records, $vendedorId);
            if ($vendedorName === null) return null;

            $orderIds = collect($records)->pluck('id')->all();
            $cargos   = $this->getCargosByOrders($orderIds);
            $adendas  = $this->getAdendas([$vendedorId], $year, $month);

            $totalOtf  = $this->sumCargos($cargos['otf']);
            $totalMrc  = $this->sumCargos($cargos['mrc']);
            $adendaAmt = $adendas[$vendedorId] ?? 0;

            $periodo = ucfirst(
                \Carbon\Carbon::createFromDate((int) $year, (int) $month, 1)
                    ->locale('es')
                    ->translatedFormat('F Y')
            );

            return [
                'vendedor_id' => $vendedorId,
                'vendedor'    => $vendedorName,
                'periodo'     => $periodo,
                'otf'         => round($totalOtf, 2),
                'mrc'         => round($totalMrc, 2),
                'adendas'     => round($adendaAmt, 2),
                'total'       => round($totalOtf + $totalMrc + $adendaAmt, 2),
            ];
        });
    }

    // ── Por vendedor / año ────────────────────────────────────

    public function getForVendedorYear(int $vendedorId, string $year): ?array
    {
        $cacheKey = "commissions:vendedor:{$vendedorId}:year:{$year}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($vendedorId, $year) {
            $records = $this->odoo->execute('sale.order', 'search_read',
                [[
                    ['user_id',                   '=', $vendedorId],
                    ['year_comission',            '=', $year],
                    ['to_invoice_button_clicked', '=', true],
                    ['state',                     '!=', 'cancel'],
                ]],
                ['fields' => ['name', 'user_id', 'month_comission', 'amount_total', 'date_order'], 'limit' => 0]
            ) ?? [];

            $vendedorName = $this->resolveVendedorName($records, $vendedorId);
            if ($vendedorName === null) return null;

            $orderIds      = collect($records)->pluck('id')->all();
            $cargos        = $this->getCargosByOrders($orderIds);
            $ordersByMonth = collect($records)->groupBy(fn($r) => (int) $r['month_comission']);
            $adendasYear   = $this->getAdendasForYear([$vendedorId], $year);

            $meses = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthOrderIds = $ordersByMonth->get($m, collect())->pluck('id')->all();

                $otf = collect($monthOrderIds)
                    ->reduce(fn($c, $id) => $c + ($cargos['otf'][$id] ?? collect())->sum('sub_amount'), 0.0);
                $mrc = collect($monthOrderIds)
                    ->reduce(fn($c, $id) => $c + ($cargos['mrc'][$id] ?? collect())->sum('sub_amount'), 0.0);

                $adenda = $adendasYear[$m][$vendedorId] ?? 0;

                $meses[] = [
                    'mes'    => $m,
                    'label'  => self::MONTH_LABELS[$m],
                    'otf'    => round($otf, 2),
                    'mrc'    => round($mrc, 2),
                    'adendas'=> round($adenda, 2),
                    'total'  => round($otf + $mrc + $adenda, 2),
                ];
            }

            $totales = [
                'otf'    => round(array_sum(array_column($meses, 'otf')), 2),
                'mrc'    => round(array_sum(array_column($meses, 'mrc')), 2),
                'adendas'=> round(array_sum(array_column($meses, 'adendas')), 2),
                'total'  => round(array_sum(array_column($meses, 'total')), 2),
            ];

            return [
                'vendedor_id' => $vendedorId,
                'vendedor'    => $vendedorName,
                'year'        => (int) $year,
                'meses'       => $meses,
                'totales'     => $totales,
            ];
        });
    }

    // ── Helpers ───────────────────────────────────────────────

    private function resolveVendedorName(array $records, int $vendedorId): ?string
    {
        if (!empty($records)) {
            $uid = $records[0]['user_id'];
            if (is_array($uid)) return $uid[1];
        }

        $users = $this->odoo->execute('res.users', 'search_read',
            [[['id', '=', $vendedorId]]],
            ['fields' => ['name'], 'limit' => 1]
        ) ?? [];

        return $users[0]['name'] ?? null;
    }

    private function sumCargos(\Illuminate\Support\Collection $grouped): float
    {
        return $grouped->reduce(fn($carry, $group) => $carry + $group->sum('sub_amount'), 0.0);
    }

    // ── Caché ─────────────────────────────────────────────────

    public function clearCache(string $year, string $month): void
    {
        Cache::forget("commissions:period:{$year}:{$month}");
    }

    public function clearCacheYear(string $year): void
    {
        Cache::forget("commissions:year:{$year}");
    }

    public function odoo(): OdooService
    {
        return $this->odoo;
    }

    
}