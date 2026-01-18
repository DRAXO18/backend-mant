<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use App\Models\CompanyUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class AuthBootstrapService
{
    public function bootstrap(string $accessToken): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Obtener usuario autenticado desde Supabase
        |--------------------------------------------------------------------------
        */
        $sbUserResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'apikey' => config('services.supabase.key'),
        ])->get(
            rtrim(config('services.supabase.url'), '/') . '/auth/v1/user'
        );

        if (! $sbUserResp->successful()) {
            return; // token inválido, no bootstrap
        }

        $sbUser = $sbUserResp->json();
        $sbUserId = $sbUser['id']; // ID del usuario en Supabase
        $email = $sbUser['email'];

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ ¿Existe el usuario en Laravel?
        |--------------------------------------------------------------------------
        */
        $user = User::where('supabase_user_id', $sbUserId)->first();

        if ($user) {
            return; // ya bootstrapado
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Preguntar a Kardex/Supabase DB: empresa asignada
        |--------------------------------------------------------------------------
        | AQUÍ tú llamas a tu API del Kardex o a una RPC
        */
        $empresaResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'apikey' => config('services.supabase.key'),
            'Content-Type' => 'application/json',
        ])->post(
            rtrim(config('services.supabase.url'), '/') . '/rest/v1/rpc/get_my_company'
        );


        if (! $empresaResp->successful() || empty($empresaResp->json())) {
            return; // usuario sin empresa asignada
        }

        $empresaData = $empresaResp->json()[0];
        $empresaNombre = $empresaData['nombre'];


        /*
        |--------------------------------------------------------------------------
        | 4️⃣ Crear usuario + empresa + relación (una sola vez)
        |--------------------------------------------------------------------------
        */
        DB::transaction(function () use ($sbUserId, $email, $empresaNombre) {

            $user = User::create([
                'name' => $email,
                'email' => $email,
                'status' => 1,
                'supabase_user_id' => $sbUserId,
            ]);

            $company = Company::firstOrCreate(
                ['name' => $empresaNombre],
                ['status' => 1]
            );

            CompanyUser::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                ],
                [
                    'status' => 1,
                ]
            );
        });
    }
}
