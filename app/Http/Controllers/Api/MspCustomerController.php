<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MspService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MspCustomerController extends Controller
{
    public function __construct(protected MspService $msp) {}

    public function findByRuc(Request $request): JsonResponse
    {
        $request->validate(['ruc' => 'required|string|min:3']);

        try {
            $customers = $this->msp->findCustomerByRuc($request->ruc);
            return response()->json(['success' => true, 'data' => $customers]);
        } catch (\Exception $e) {
            Log::error('MSP findCustomerByRuc failed', ['ruc' => $request->ruc, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al consultar el cliente.'], 500);
        }
    }
}
