<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    // LOGIN NORMAL → GUARDA JWT EN COOKIE httpOnly
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');


        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // ✅ Crear cookie httpOnly con el JWT
        $cookie = Cookie::make(
            'token',        // nombre
            $token,         // valor (JWT)
            60 * 24 * 7,    // 7 días
            '/',            // path
            null,           // domain
            false,          // secure (true en HTTPS)
            true,           // ✅ httpOnly
            false,
            'Strict'
        );

        return response()->json([
            'message' => 'Usuario logueado exitosamente',
            'user'    => Auth::user()
        ])->withCookie($cookie);
    }

    // LOGOUT → INVALIDA JWT + BORRA COOKIE
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        $cookie = Cookie::forget('token');

        return response()->json([
            'message' => 'Sesión cerrada'
        ])->withCookie($cookie);
    }

    // PERFIL DEL USUARIO (PROTEGIDO)
    public function me(Request $request)
    {
        $user = Auth::user() ?? $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // 🧠 Inferencia de paneles (NO persistente)
        $panels = [];

        // Panel CLIENTE
        if (method_exists($user, 'client') && $user->client()->exists()) {
            $panels[] = 'client';
        }

        // Panel EMPRESA (admin o técnico)
        if (
            (method_exists($user, 'companyUsers') && $user->companyUsers()->exists()) ||
            (method_exists($user, 'technicians') && $user->technicians()->exists())
        ) {
            $panels[] = 'company';
        }

        // Panel RUBRO
        if (method_exists($user, 'rubroUser') && $user->rubroUser()->exists()) {
            $panels[] = 'rubro';
        }

        // 🧭 Panel activo por defecto
        $activePanel = $panels[0] ?? null;

        return response()->json([
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $user->avatar,
            ],
            'panels' => $panels,
            'active_panel' => $activePanel,
        ]);
    }

    public function test()
    {
        return response()->json([
            'success' => true,
            'message' => 'El usuario tiene acceso al panel COMPANY',
        ]);
    }
}
