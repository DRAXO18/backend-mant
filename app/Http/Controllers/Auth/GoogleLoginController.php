<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            logger('✅ Entrando a handleGoogleCallback');

            $googleUser = Socialite::driver('google')->stateless()->user();

            logger('✅ Usuario recibido desde Google', [
                'id'     => $googleUser->getId(),
                'email'  => $googleUser->getEmail(),
                'name'   => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            $googleId = $googleUser->getId();
            $email    = $googleUser->getEmail();
            $name     = $googleUser->getName();
            $avatar   = $googleUser->getAvatar();

            $user = User::where('google_id', $googleId)
                ->orWhere('email', $email)
                ->first();

            if (!$user) {
                logger('🆕 Usuario no existe, creando nuevo usuario');

                $user = User::create([
                    'name'              => $name,
                    'email'             => $email,
                    'google_id'         => $googleId,
                    'avatar'            => $avatar,
                    'password'          => bcrypt(str()->random(32)),
                    'email_verified_at' => now(),
                ]);
            } else {
                logger('Usuario ya existente', [
                    'user_id' => $user->id
                ]);

                if (!$user->google_id) {
                    $user->google_id = $googleId;
                }

                if ($avatar) {
                    $user->avatar = $avatar;
                }

                $user->save();
            }

            logger('✅ Usuario listo para generar JWT', [
                'user_id' => $user->id,
            ]);

            // ✅ GENERAR JWT
            $token = JWTAuth::fromUser($user);

            logger('JWT generado correctamente', [
                'token' => $token,
            ]);

            // ✅ CREAR COOKIE
            $cookie = cookie(
                'token',
                $token,
                60,
                '/',
                null,
                true,
                true,
                false,
                'Lax'
            );

            logger('✅ Cookie creada', [
                'cookie_name' => $cookie->getName(),
                'cookie_value' => $token,
            ]);

            logger('🚀 Redirigiendo a dashboard...');

            return redirect('http://localhost:5173/client/dashboard')
                ->withCookie($cookie);
        } catch (\Throwable $e) {
            logger('ERROR en Google Login', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error al autenticarse con Google',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
