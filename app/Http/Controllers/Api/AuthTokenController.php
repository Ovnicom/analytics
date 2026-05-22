<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthTokenController extends Controller
{
    /**
     * POST /api/v1/auth/token
     * Genera un Bearer token con vida de 30 días.
     */
    public function issue(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        // Revocar tokens anteriores con el mismo nombre (evita acumulación)
        $user->tokens()->where('name', 'api')->delete();

        $token = $user->createToken('api', ['*'], now()->addDays(30));

        return response()->json([
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at->toDateTimeString(),
        ], 201);
    }

    /**
     * DELETE /api/v1/auth/token
     * Revoca el token actual.
     */
    public function revoke(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Token revocado correctamente.']);
    }
}
