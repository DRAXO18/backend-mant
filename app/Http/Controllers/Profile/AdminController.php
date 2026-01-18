<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use App\Models\CompanyUser;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', 'active'); // active | deleted | all

        $admins = Admin::query()
            ->with('user')
            ->when($status === 'deleted', fn($q) => $q->onlyTrashed())
            ->when($status === 'all', fn($q) => $q->withTrashed())
            ->when($q !== '', function ($query) use ($q) {
                $query->where('uid', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%");
                    });
            })
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'ok' => true,
            'filters' => [
                'status' => $status,
                'q' => $q,
            ],
            'data' => $admins,
        ]);
    }

    public function store(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | 0️⃣ Contexto del owner (inyectado por middleware)
    |--------------------------------------------------------------------------
    */
        $ownerSupabaseUid = $request->attributes->get('supabase_uid');
        $accessToken = $request->cookie('sb_access_token');

        if (! $ownerSupabaseUid || ! $accessToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Owner en Laravel
        $owner = User::where('supabase_user_id', $ownerSupabaseUid)->firstOrFail();

        // Empresa del owner en Laravel (solo para relación local)
        $companyLaravel = $owner->companies()->first();
        if (! $companyLaravel) {
            return response()->json(['message' => 'Empresa no encontrada'], 403);
        }

        /*
    |--------------------------------------------------------------------------
    | 1️⃣ Obtener empresa REAL desde Supabase (RPC get_my_company)
    |--------------------------------------------------------------------------
    */
        $empresaResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'apikey'        => config('services.supabase.key'),
            'Content-Type'  => 'application/json',
        ])->post(
            rtrim(config('services.supabase.url'), '/') . '/rest/v1/rpc/get_my_company'
        );

        if (! $empresaResp->successful() || empty($empresaResp->json())) {
            return response()->json(['message' => 'Empresa Supabase no encontrada'], 403);
        }

        $empresaSb = $empresaResp->json()[0];
        $empresaSupabaseId = $empresaSb['empresa_id']; // 👈 ESTE es el ID correcto

        /*
    |--------------------------------------------------------------------------
    | 2️⃣ Validación
    |--------------------------------------------------------------------------
    */
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'uid'      => ['nullable', 'string', 'max:50', 'unique:admins,uid'],
        ]);

        /*
    |--------------------------------------------------------------------------
    | 3️⃣ Crear usuario en Supabase Auth
    |--------------------------------------------------------------------------
    */
        $sbResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.supabase.service_role_key'),
            'apikey'        => config('services.supabase.service_role_key'),
            'Content-Type'  => 'application/json',
        ])->post(
            rtrim(config('services.supabase.url'), '/') . '/auth/v1/admin/users',
            [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'email_confirm' => true,
            ]
        );

        if (! $sbResp->successful()) {
            return response()->json([
                'message' => 'Error creando usuario en Supabase',
                'error' => $sbResp->json(),
            ], 500);
        }

        $supabaseUserId = $sbResp->json()['id'];

        /*
    |--------------------------------------------------------------------------
    | 4️⃣ Crear usuario de dominio en Supabase (RPC)
    |--------------------------------------------------------------------------
    */
        $rpcResp = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.supabase.service_role_key'),
            'apikey'        => config('services.supabase.service_role_key'),
            'Content-Type'  => 'application/json',
        ])->post(
            rtrim(config('services.supabase.url'), '/') . '/rest/v1/rpc/create_usuario_y_asignar_empresa',
            [
                'p_auth_id'    => $supabaseUserId,
                'p_email'      => $validated['email'],
                'p_nombre'     => $validated['name'],
                'p_empresa_id' => $empresaSupabaseId,
                'p_tipouser'   => 'admin',
            ]
        );

        if (! $rpcResp->successful()) {
            return response()->json([
                'message' => 'Error creando usuario de dominio en Supabase',
                'error' => $rpcResp->json(),
            ], 500);
        }

        /*
    |--------------------------------------------------------------------------
    | 5️⃣ Crear espejo en Laravel (transacción)
    |--------------------------------------------------------------------------
    */
        $result = DB::transaction(function () use (
            $validated,
            $supabaseUserId,
            $companyLaravel
        ) {

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => 1,
                'supabase_user_id' => $supabaseUserId,
            ]);

            $admin = Admin::create([
                'user_id' => $user->id,
                'uid' => $validated['uid'] ?? $this->generateAdminUid(),
            ]);

            CompanyUser::firstOrCreate(
                [
                    'company_id' => $companyLaravel->id,
                    'user_id' => $user->id,
                ],
                [
                    'status' => 1,
                ]
            );

            return $admin->load('user');
        });

        return response()->json([
            'ok' => true,
            'message' => 'Admin creado correctamente',
            'data' => $result,
        ], 201);
    }


    public function update(Request $request, int $id)
    {
        $admin = Admin::with('user')->findOrFail($id);

        $validated = $request->validate([
            'name'  => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($admin->user_id),
            ],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],

            'uid' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('admins', 'uid')->ignore($admin->id),
            ],
        ]);

        $result = DB::transaction(function () use ($admin, $validated) {
            // user update
            $userData = array_intersect_key($validated, array_flip(['name', 'email', 'phone']));
            if (!empty($userData)) {
                $admin->user->fill($userData);
            }

            if (array_key_exists('password', $validated) && $validated['password']) {
                $admin->user->password = Hash::make($validated['password']);
            }

            $admin->user->save();

            // admin update
            if (array_key_exists('uid', $validated)) {
                $admin->uid = $validated['uid'];
                $admin->save();
            }

            return $admin->fresh()->load('user');
        });

        return response()->json([
            'ok' => true,
            'message' => 'Admin updated successfully.',
            'data' => $result,
        ]);
    }

    public function destroy(int $id)
    {
        $admin = Admin::findOrFail($id);

        DB::transaction(function () use ($admin) {
            $admin->delete(); // soft delete profile
            // optional: $admin->user->update(['status' => 0]);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Admin deleted (soft) successfully.',
        ]);
    }

    private function generateAdminUid(): string
    {
        $lastId = (int) (Admin::withTrashed()->max('id') ?? 0) + 1;
        return 'ADM-' . str_pad((string) $lastId, 6, '0', STR_PAD_LEFT);
    }
}
