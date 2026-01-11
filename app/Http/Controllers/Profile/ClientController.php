<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', 'active'); // active | deleted | all

        $clients = Client::query()
            ->with('user')
            ->when($status === 'deleted', fn($q) => $q->onlyTrashed())
            ->when($status === 'all', fn($q) => $q->withTrashed())
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('user', function ($uq) use ($q) {
                    $uq->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
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
            'data' => $clients,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // user fields
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],

            // client fields
            'ubigeo_id' => ['nullable', 'integer', 'exists:ubigeo,id'],
            'address'   => ['nullable', 'string', 'max:255'],
        ]);

        $result = DB::transaction(function () use ($validated) {
            // 1) create user
            $user = User::create([
                'name'   => $validated['name'],
                'phone'  => $validated['phone'] ?? null,
                'email'  => $validated['email'] ?? null,
                'status' => 1, // active (ajusta si tu sistema usa otra lógica)
                // password: NO requerido para cliente (si tu users exige password NOT NULL, avísame)
            ]);

            // 2) create client profile
            $client = Client::create([
                'user_id'   => $user->id,
                'ubigeo_id' => $validated['ubigeo_id'] ?? null,
                'address'   => $validated['address'] ?? null,
            ]);

            return $client->load('user');
        });

        return response()->json([
            'ok' => true,
            'message' => 'Client created successfully.',
            'data' => $result,
        ], 201);
    }

    /**
     * Update client + user via transaction.
     */
    public function update(Request $request, int $id)
    {
        $client = Client::with('user')->findOrFail($id);

        $validated = $request->validate([
            // user fields
            'name'  => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($client->user_id),
            ],

            // client fields
            'ubigeo_id' => ['sometimes', 'required', 'integer', 'exists:ubigeo,id'],
            'address'   => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $result = DB::transaction(function () use ($client, $validated) {
            // Update user (only if provided)
            $userData = array_intersect_key($validated, array_flip(['name', 'phone', 'email']));
            if (!empty($userData)) {
                $client->user->fill($userData);
                $client->user->save();
            }

            // Update client profile (only if provided)
            $clientData = array_intersect_key($validated, array_flip(['ubigeo_id', 'address']));
            if (!empty($clientData)) {
                $client->fill($clientData);
                $client->save();
            }

            return $client->fresh()->load('user');
        });

        return response()->json([
            'ok' => true,
            'message' => 'Client updated successfully.',
            'data' => $result,
        ]);
    }

    /**
     * Soft delete client (and optionally user? -> aquí NO lo borro para no romper historial).
     * Uses transaction.
     */
    public function destroy(int $id)
    {
        $client = Client::findOrFail($id);

        DB::transaction(function () use ($client) {
            $client->delete(); // soft delete
        });

        return response()->json([
            'ok' => true,
            'message' => 'Client deleted (soft) successfully.',
        ]);
    }
}
