<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MspClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MspClientController extends Controller
{
    /**
     * POST /api/v1/msp-clients/bulk-update
     *
     * Actualiza email_cliente y/o numero_cuenta de uno o varios clientes MSP.
     * Solo actualiza clientes que ya existen en la tabla (no crea nuevos).
     * Ignora filas sin campos que actualizar.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'clients'                 => 'required|array|min:1|max:1000',
            'clients.*.customer_name' => 'required|string|max:255',
            'clients.*.email_cliente' => 'nullable|email|max:255',
            'clients.*.numero_cuenta' => 'nullable|string|max:100',
        ]);

        $updated = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($request->input('clients') as $item) {
            try {
                $name = trim($item['customer_name']);

                $fields = array_filter([
                    'email_cliente' => $item['email_cliente'] ?? null,
                    'numero_cuenta' => $item['numero_cuenta'] ?? null,
                ], fn($v) => $v !== null && $v !== '');

                if (empty($fields)) {
                    $skipped++;
                    continue;
                }

                $rows = MspClient::where('customer_name', $name)->update($fields);

                if ($rows > 0) {
                    $updated++;
                } else {
                    $skipped++; // cliente no existe en MSP
                }

            } catch (\Throwable $e) {
                $errors[] = ($item['customer_name'] ?? '?') . ': ' . $e->getMessage();
            }
        }

        return response()->json([
            'updated' => $updated,
            'skipped' => $skipped,
            'errors'  => $errors,
            'total'   => count($request->input('clients')),
        ]);
    }
}
