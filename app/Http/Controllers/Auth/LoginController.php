<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AuthBootstrapService;


class LoginController extends Controller
{

    public function login(Request $request, AuthBootstrapService $bootstrap)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // 1️⃣ Login Supabase (IGUAL)
        $response = Http::withHeaders([
            'apikey'       => config('services.supabase.key'),
            'Content-Type' => 'application/json',
        ])->post(
            config('services.supabase.url') . '/auth/v1/token?grant_type=password',
            [
                'email'    => $request->email,
                'password' => $request->password,
            ]
        );

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $data = $response->json();

        $accessToken  = $data['access_token'];
        $refreshToken = $data['refresh_token'];
        $expiresIn    = $data['expires_in'];

        // 2️⃣ 👉 NUEVO: bootstrap Laravel (NO rompe nada)
        $bootstrap->bootstrap($accessToken);

        // 3️⃣ Cookies (IGUAL)
        $accessCookie = Cookie::make(
            'sb_access_token',
            $accessToken,
            intval($expiresIn / 60),
            '/',
            null,
            true,
            true,
            false,
            'Strict'
        );

        $refreshCookie = Cookie::make(
            'sb_refresh_token',
            $refreshToken,
            60 * 24 * 30,
            '/',
            null,
            true,
            true,
            false,
            'Strict'
        );

        return response()->json([
            'message' => 'Login exitoso',
        ])->withCookie($accessCookie)
            ->withCookie($refreshCookie);
    }


    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        $cookie = Cookie::forget('token');

        return response()->json([
            'message' => 'Sesión cerrada'
        ])->withCookie($cookie);
    }

    public function me(Request $request)
    {
        $user = Auth::user() ?? $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        return response()->json([
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $user->avatar,
            ],
        ]);
    }
}
