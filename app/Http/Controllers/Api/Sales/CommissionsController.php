<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\CommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommissionsController extends Controller
{
    public function __construct(private CommissionService $commissions) {}

    /**
     * GET /api/v1/commissions/{vendedor_id}/month
     * Query params: year (default: current), month (default: current)
     */
    public function month(Request $request, int $vendedor_id): JsonResponse
    {
        $request->validate([
            'year'  => ['sometimes', 'integer', 'min:2020', 'max:2099'],
            'month' => ['sometimes', 'integer', 'min:1', 'max:12'],
        ]);

        $year  = (string) ($request->integer('year',  now()->year));
        $month = (string) ($request->integer('month', now()->month));

        $data = $this->commissions->getForVendedorMonth($vendedor_id, $year, $month);

        if ($data === null) {
            return response()->json(
                ['message' => 'Vendedor no encontrado o sin comisiones en este período.'],
                404
            );
        }

        return response()->json($data);
    }

    /**
     * GET /api/v1/commissions/{vendedor_id}/year
     * Query params: year (default: current)
     */
    public function year(Request $request, int $vendedor_id): JsonResponse
    {
        $request->validate([
            'year' => ['sometimes', 'integer', 'min:2020', 'max:2099'],
        ]);

        $year = (string) ($request->integer('year', now()->year));

        $data = $this->commissions->getForVendedorYear($vendedor_id, $year);

        if ($data === null) {
            return response()->json(
                ['message' => 'Vendedor no encontrado o sin comisiones en este año.'],
                404
            );
        }

        return response()->json($data);
    }
}
