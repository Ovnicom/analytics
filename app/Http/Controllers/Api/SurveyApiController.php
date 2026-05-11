<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyType;
use Illuminate\Http\Request;

class SurveyApiController extends Controller
{
    public function receive(Request $request, string $token)
    {
        // El token de la URL es la autenticación del webhook — debe tener exactamente 64 chars hex
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return response()->json(['message' => 'Token inválido.'], 401);
        }

        $type = SurveyType::where('token', $token)
                          ->where('activo', true)
                          ->first();

        if (!$type) {
            // Respuesta genérica para no revelar si el token existe pero está inactivo
            return response()->json(['message' => 'Token inválido.'], 401);
        }

        // Construir data con solo los campos declarados en el tipo — ignorar campos extra
        $data = [];
        foreach ($type->campos as $campo) {
            $valor = $request->input($campo);
            $data[$campo] = is_string($valor) ? strip_tags(trim($valor)) : $valor;
        }

        Survey::create([
            'survey_type_id'  => $type->id,
            'fecha'           => now()->toDateString(),
            'numero_whatsapp' => strip_tags(trim((string) $request->input('numero_whatsapp', ''))),
            'nombre'          => strip_tags(trim((string) $request->input('nombre', 'Sin nombre'))),
            'data'            => $data,
        ]);

        return response()->json(['message' => 'Encuesta guardada.'], 201);
    }
}