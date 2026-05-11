<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SurveyApiController;
use App\Http\Controllers\Api\Sales\CommissionsController;

// ── Encuestas (webhook público — autenticado por token en URL) ────────────────
Route::middleware('throttle:30,1')
    ->post('/surveys/{token}', [SurveyApiController::class, 'receive']);

// ── Ventas / Comisiones ───────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:60,1'])
    ->prefix('v1/commissions')
    ->group(function () {
        Route::get('/{vendedor_id}/month', [CommissionsController::class, 'month'])
            ->whereNumber('vendedor_id');
        Route::get('/{vendedor_id}/year',  [CommissionsController::class, 'year'])
            ->whereNumber('vendedor_id');
    });