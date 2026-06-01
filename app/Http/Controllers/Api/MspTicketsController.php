<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MspService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MspTicketsController extends Controller
{
    public function __construct(protected MspService $msp) {}

    /**
     * GET /api/v1/msp/search?ruc=XXXXXXXX
     * Búsqueda unificada: cliente + tickets + ticket_users + responses + SLAs.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['ruc' => 'required|string|min:3']);

        try {
            $result = $this->msp->unifiedSearch($request->ruc);

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/msp/tickets?customerId=XXXXXXXX
     * Tickets + ticket_users de un cliente por CustomerId.
     */
    public function tickets(Request $request): JsonResponse
    {
        $request->validate(['customerId' => 'required|string']);

        try {
            $result = $this->msp->fetchTicketsByCustomer($request->customerId);

            return response()->json([
                'success'   => true,
                'data'      => $result['data'],
                'ticketIds' => $result['ticketIds'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/msp/ticket-details?ticketIds[]=xxx&ticketIds[]=yyy
     * Responses + SLAs por TicketId.
     */
    public function ticketDetails(Request $request): JsonResponse
    {
        $request->validate([
            'ticketIds'   => 'required|array|min:1',
            'ticketIds.*' => 'string',
        ]);

        try {
            $result = $this->msp->fetchTicketDetails($request->ticketIds);

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
