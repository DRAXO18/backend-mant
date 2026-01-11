<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;

use App\Models\Owner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;


class OwnerController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', 'active'); // active | deleted | all

        $owners = Owner::query()
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
            'data' => $owners,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // user fields
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],

            // identification
            'identification_type' => ['required', 'string', 'exists:identification_types,code'],
            'number' => ['required', 'string', 'max:50'],
        ]);

        $identificationTypeId = DB::table('identification_types')
            ->where('code', $validated['identification_type'])
            ->value('id');

        $numberHash = hash('sha256', $validated['number']);

        // Unicidad real (dinámica)
        $exists = DB::table('user_identifications')
            ->where('identification_type_id', $identificationTypeId)
            ->where('number_hash', $numberHash)
            ->exists();

        if ($exists) {
            return response()->json([
                'ok' => false,
                'message' => 'Este número de identificación ya está registrado.',
            ], 422);
        }

        $result = DB::transaction(function () use ($validated, $request, $identificationTypeId, $numberHash) {

            $user = User::create([
                'name'   => $validated['name'],
                'phone'  => $validated['phone'] ?? null,
                'email'  => $validated['email'] ?? null,
                'status' => 1,
            ]);

            Owner::create([
                'user_id' => $user->id,
            ]);

            DB::table('user_identifications')->insert([
                'user_id' => $user->id,
                'identification_type_id' => $identificationTypeId,
                'number_hash' => $numberHash,
                'number_encrypted' => Crypt::encryptString($validated['number']),
                'issued_at' => null,
                'expires_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return Owner::with('user')->where('user_id', $user->id)->first();
        });

        return response()->json([
            'ok' => true,
            'message' => 'Owner created successfully.',
            'data' => $result,
        ], 201);
    }


    /**
     * Update owner user fields (owner has no extra fields).
     */
    public function update(Request $request, int $id)
    {
        $owner = Owner::with('user')->findOrFail($id);

        $validated = $request->validate([
            'name'  => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($owner->user_id),
            ],
        ]);

        $result = DB::transaction(function () use ($owner, $validated) {
            $owner->user->fill($validated);
            $owner->user->save();

            return $owner->fresh()->load('user');
        });

        return response()->json([
            'ok' => true,
            'message' => 'Owner updated successfully.',
            'data' => $result,
        ]);
    }

    /**
     * Soft delete owner profile only.
     */
    public function destroy(int $id)
    {
        $owner = Owner::findOrFail($id);

        DB::transaction(function () use ($owner) {
            $owner->delete();
        });

        return response()->json([
            'ok' => true,
            'message' => 'Owner deleted (soft) successfully.',
        ]);
    }
}
