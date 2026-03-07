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

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $user = auth()->user();

        $ttl = JWTAuth::factory()->getTTL();

        $cookie = Cookie::make(
            'token',
            $token,
            $ttl,
            '/',
            null,
            false,
            true,
            false,
            'Strict'
        );

        return response()->json([
            'message' => 'Login exitoso'
        ])->withCookie($cookie);
    }

    public function me(Request $request)
    {
        $user = $request->user();

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
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    // Supabase Auth
    // public function login(Request $request, AuthBootstrapService $bootstrap)
    // {
    //     $request->validate([
    //         'email'    => 'required|email',
    //         'password' => 'required|string|min:6',
    //     ]);

    //     // 1️⃣ Login Supabase (IGUAL)
    //     $response = Http::withHeaders([
    //         'apikey'       => config('services.supabase.key'),
    //         'Content-Type' => 'application/json',
    //     ])->post(
    //         config('services.supabase.url') . '/auth/v1/token?grant_type=password',
    //         [
    //             'email'    => $request->email,
    //             'password' => $request->password,
    //         ]
    //     );

    //     if (! $response->successful()) {
    //         return response()->json([
    //             'message' => 'Credenciales incorrectas'
    //         ], 401);
    //     }

    //     $data = $response->json();

    //     $accessToken  = $data['access_token'];
    //     $refreshToken = $data['refresh_token'];
    //     $expiresIn    = $data['expires_in'];

    //     // 2️⃣ 👉 NUEVO: bootstrap Laravel (NO rompe nada)
    //     $bootstrap->bootstrap($accessToken);

    //     // 3️⃣ Cookies (IGUAL)
    //     $accessCookie = Cookie::make(
    //         'sb_access_token',
    //         $accessToken,
    //         intval($expiresIn / 60),
    //         '/',
    //         null,
    //         true,
    //         true,
    //         false,
    //         'Strict'
    //     );

    //     $refreshCookie = Cookie::make(
    //         'sb_refresh_token',
    //         $refreshToken,
    //         60 * 24 * 30,
    //         '/',
    //         null,
    //         true,
    //         true,
    //         false,
    //         'Strict'
    //     );

    //     return response()->json([
    //         'message' => 'Login exitoso',
    //     ])->withCookie($accessCookie)
    //         ->withCookie($refreshCookie);
    // }

    // public function bootstrap(Request $request, AuthBootstrapService $bootstrap)
    // {
    //     $token = $request->bearerToken();

    //     if (! $token) {
    //         return response()->json(['message' => 'Unauthorized'], 401);
    //     }

    //     $bootstrap->bootstrap($token);

    //     return response()->json([
    //         'ok' => true,
    //         'message' => 'Bootstrap completado'
    //     ]);
    // }

    public function logout(Request $request)
    {
        // 1️⃣ Borrar cookies Supabase
        $accessCookie = Cookie::forget('sb_access_token');
        $refreshCookie = Cookie::forget('sb_refresh_token');

        return response()->json([
            'message' => 'Sesión cerrada',
            // frontend de mantenimiento sabrá que debe redirigir a Kardex
            'logout_kardex' => true,
        ])->withCookie($accessCookie)
            ->withCookie($refreshCookie);
    }
}
