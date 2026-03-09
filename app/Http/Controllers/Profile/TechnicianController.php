<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;

use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;

class TechnicianController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', 'active'); // active | deleted | all

        $technicians = Technician::query()
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
            'data' => $technicians,
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            // user
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],

            // identification
            'identification_type' => ['required', 'string', 'exists:identification_types,code'],
            'number' => ['required', 'string', 'max:50'],

            // technician
            'specialty' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
        ]);

        $identificationTypeId = DB::table('identification_types')
            ->where('code', $validated['identification_type'])
            ->value('id');

        $numberHash = hash('sha256', $validated['number']);

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

        $result = DB::transaction(function () use ($validated, $identificationTypeId, $numberHash, $request) {

            $user = User::create([
                'name'   => $validated['name'],
                'phone'  => $validated['phone'] ?? null,
                'email'  => $validated['email'] ?? null,
                'status' => 1,
            ]);

            $technician = Technician::create([
                'user_id' => $user->id,
                'company_id' => $request->user()->company_id ?? null,
                'specialty' => $validated['specialty'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
                'experience_years' => $validated['experience_years'] ?? null,
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

            return Technician::with('user')->find($technician->id);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Technician created successfully.',
            'data' => $result,
        ], 201);
    }


    public function update(Request $request, int $id)
    {
        $technician = Technician::with('user')->findOrFail($id);

        $validated = $request->validate([
            'name'  => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($technician->user_id),
            ],

            'specialty' => ['sometimes', 'nullable', 'string', 'max:255'],
            'license_number' => ['sometimes', 'nullable', 'string', 'max:100'],
            'experience_years' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ]);

        $result = DB::transaction(function () use ($technician, $validated) {

            $technician->user->fill($validated);
            $technician->user->save();

            $technician->fill([
                'specialty' => $validated['specialty'] ?? $technician->specialty,
                'license_number' => $validated['license_number'] ?? $technician->license_number,
                'experience_years' => $validated['experience_years'] ?? $technician->experience_years,
            ]);

            $technician->save();

            return $technician->fresh()->load('user');
        });

        return response()->json([
            'ok' => true,
            'message' => 'Technician updated successfully.',
            'data' => $result,
        ]);
    }


    public function destroy(int $id)
    {
        $technician = Technician::findOrFail($id);

        DB::transaction(function () use ($technician) {
            $technician->delete();
        });

        return response()->json([
            'ok' => true,
            'message' => 'Technician deleted (soft) successfully.',
        ]);
    }
}