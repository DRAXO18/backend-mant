<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SupabaseAuth
{
    public function handle(Request $request, Closure $next)
    {
        // 1️⃣ Buscar token en Authorization Bearer
        $token = $request->bearerToken();

        // 2️⃣ Si no hay bearer, buscar cookie
        if (! $token) {
            $token = $request->cookie('sb_access_token');
        }

        // 3️⃣ Si no hay token en ningún lado → fuera
        if (! $token) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        // 4️⃣ JWT debe tener 3 partes
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return response()->json([
                'message' => 'Token inválido'
            ], 401);
        }

        try {
            $payload = json_decode(
                base64_decode(strtr($parts[1], '-_', '+/')),
                true
            );

            if (!is_array($payload)) {
                return response()->json(['message' => 'Token inválido'], 401);
            }

            // 5️⃣ Expiración
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return response()->json(['message' => 'Token expirado'], 401);
            }

            // 6️⃣ UID Supabase
            if (empty($payload['sub'])) {
                return response()->json(['message' => 'Token inválido'], 401);
            }

            // 7️⃣ Guardar contexto
            $request->attributes->set('supabase_uid', $payload['sub']);

        } catch (\Throwable $e) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        return $next($request);
    }
}

