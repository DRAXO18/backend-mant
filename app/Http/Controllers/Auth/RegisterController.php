<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // VALIDACIÓN ESTRICTA
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$/',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
            ],

            // Validación del tipo de documento
            'identification_type_code' => [
                'required',
                'string',
                'exists:identification_types,code',
            ],

            // Validación del número de documento
            'number' => [
                'required',
                'string',
                'min:6', // se puede ajustar según tipo de doc
            ],
        ], [
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'identification_type_code.exists' => 'El tipo de documento no es válido.',
        ]);

        try {
            DB::beginTransaction();

            // 1️⃣ ENCONTRAR EL TIPO DE DOCUMENTO
            $identificationType = \App\Models\IdentificationType::where('code', $request->identification_type_code)->firstOrFail();

            // 2️⃣ CREAR USER
            $user = User::create([
                'name'     => trim($request->name),
                'email'    => strtolower($request->email),
                'password' => Hash::make($request->password),
            ]);

            // 3️⃣ CREAR CLIENT
            $client = Client::create([
                'user_id' => $user->id,
                'status'  => 'active',
            ]);

            // 4️⃣ CREAR USER IDENTIFICATION
            $dni = $request->number;

            $userIdentification = \App\Models\UserIdentification::create([
                'user_id'               => $user->id,
                'identification_type_id' => $identificationType->id,
                'number_hash'           => hash('sha256', $dni),
                'number_encrypted'      => encrypt($dni),
                'issued_at'             => $request->issued_at ?? null,
                'expires_at'            => $request->expires_at ?? null,
            ]);

            // 5️⃣ GENERAR JWT
            $token = JWTAuth::fromUser($user);

            // 6️⃣ CREAR COOKIE httpOnly
            $cookie = Cookie::make(
                'token',
                $token,
                60 * 24 * 7, // 7 días
                '/',
                null,
                false, // cambiar a true con HTTPS
                true,
                false,
                'Strict'
            );

            DB::commit();

            return response()->json([
                'message' => 'Usuario registrado correctamente',
                'user'    => $user,
                'client'  => $client,
                'identification' => $userIdentification
            ])->withCookie($cookie);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error'   => 'Ocurrió un error durante el registro.',
                'details' => $e->getMessage(), // solo para desarrollo
            ], 500);
        }
    }
}
