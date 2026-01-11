<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
        $validated = $request->validate([
            // user fields
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],

            // admin fields
            'uid' => ['nullable', 'string', 'max:50', 'unique:admins,uid'],
        ]);

        $result = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name'   => $validated['name'],
                'email'  => $validated['email'],
                'phone'  => $validated['phone'] ?? null,
                'status' => 1,
                'password' => Hash::make($validated['password']),
            ]);

            $admin = Admin::create([
                'user_id' => $user->id,
                'uid' => $validated['uid'] ?? $this->generateAdminUid(),
            ]);

            return $admin->load('user');
        });

        return response()->json([
            'ok' => true,
            'message' => 'Admin created successfully.',
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
