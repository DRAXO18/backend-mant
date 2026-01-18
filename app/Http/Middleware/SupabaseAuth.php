<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SupabaseAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->cookie('sb_access_token');

        // 1️⃣ Debe existir token
        if (!$token) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        // 2️⃣ JWT debe tener 3 partes
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return response()->json([
                'message' => 'Token inválido'
            ], 401);
        }

        try {
            // 3️⃣ Decodificar payload (base64url)
            $payload = json_decode(
                base64_decode(strtr($parts[1], '-_', '+/')),
                true
            );

            if (!is_array($payload)) {
                return response()->json([
                    'message' => 'Token inválido'
                ], 401);
            }

            // 4️⃣ Expiración (opcional pero recomendable)
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return response()->json([
                    'message' => 'Token expirado'
                ], 401);
            }

            // 5️⃣ Debe tener sub (UID Supabase)
            if (empty($payload['sub'])) {
                return response()->json([
                    'message' => 'Token inválido'
                ], 401);
            }

            // 6️⃣ Guardar UID Supabase para usar en controllers
            $request->attributes->set('supabase_uid', $payload['sub']);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Token inválido'
            ], 401);
        }

        return $next($request);
    }
}
