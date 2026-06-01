<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MspService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MspTicketsController extends Controller
{
    public function __construct(protected MspService $msp) {}

    /**
     * GET /api/v1/msp/search?ruc=XXXXXXXX
     * Búsqueda unificada: cliente + tickets + ticket_users + responses + SLAs.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'ruc'   => 'required|string|min:3',
            'limit' => 'nullable|integer|min:1|max:500',
            'page'  => 'nullable|integer|min:1',
        ]);

        try {
            $result = $this->msp->unifiedSearch($request->ruc);

            if ($request->filled('limit')) {
                $limit           = (int) $request->limit;
                $page            = (int) ($request->page ?? 1);
                $tickets         = array_slice($result['tickets'], ($page - 1) * $limit, $limit);
                $result['tickets']      = $tickets;
                $result['total']        = count($result['tickets']);
                $result['page']         = $page;
                $result['limit']        = $limit;
            }

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('MSP unifiedSearch failed', ['ruc' => $request->ruc, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al realizar la búsqueda.'], 500);
        }
    }

    /**
     * GET /api/v1/msp/tickets?customerId=XXXXXXXX[&limit=50&page=1]
     * Tickets + ticket_users de un cliente por CustomerId.
     */
    public function tickets(Request $request): JsonResponse
    {
        $request->validate([
            'customerId' => 'required|string',
            'limit'      => 'nullable|integer|min:1|max:500',
            'page'       => 'nullable|integer|min:1',
        ]);

        try {
            $result  = $this->msp->fetchTicketsByCustomer($request->customerId);
            $tickets = $result['data']['tickets'];
            $total   = count($tickets);

            if ($request->filled('limit')) {
                $limit   = (int) $request->limit;
                $page    = (int) ($request->page ?? 1);
                $tickets = array_slice($tickets, ($page - 1) * $limit, $limit);
            }

            return response()->json([
                'success'   => true,
                'total'     => $total,
                'data'      => [
                    'tickets'      => $tickets,
                    'ticket_users' => $result['data']['ticket_users'],
                ],
                'ticketIds' => $result['ticketIds'],
            ]);
        } catch (\Exception $e) {
            Log::error('MSP fetchTicketsByCustomer failed', ['customerId' => $request->customerId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al consultar los tickets.'], 500);
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
            Log::error('MSP fetchTicketDetails failed', ['ticketIds' => $request->ticketIds, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al consultar el detalle de tickets.'], 500);
        }
    }
}
