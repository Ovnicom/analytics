<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MspService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
