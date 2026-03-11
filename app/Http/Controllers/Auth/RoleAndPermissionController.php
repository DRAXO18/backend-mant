<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionController extends Controller
{
    /**
     * Crear un rol dinámico
     */
    public function createRoles(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:50',
            'guard_name' => 'required|string|max:50',
        ]);

        $role = Role::firstOrCreate($data);

        return response()->json([
            'message' => 'Rol creado correctamente',
            'role'    => $role,
        ], 201);
    }

    /**
     * Crear permisos dinámicos (uno o varios)
     */
    public function createPermissions(Request $request)
    {
        $data = $request->validate([
            'permissions'          => 'required|array|min:1',
            'permissions.*.name'   => 'required|string|max:100',
            'permissions.*.guard'  => 'required|string|max:50',
        ]);

        $created = [];

        foreach ($data['permissions'] as $perm) {
            $created[] = Permission::firstOrCreate([
                'name'       => $perm['name'],
                'guard_name' => $perm['guard'],
            ]);
        }

        return response()->json([
            'message'     => 'Permisos creados correctamente',
            'permissions' => $created,
        ], 201);
    }

    /**
     * Asignar permisos a un rol (por guard)
     */
    public function assignPermissionsToRole(Request $request)
    {
        // 0️⃣ Validación de entrada (primera muralla)
        $data = $request->validate([
            'role_name'   => 'required|string',
            'guard_name'  => 'required|string',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string',
        ]);

        return DB::transaction(function () use ($data, $request) {

            // 1️⃣ Obtener el rol correcto (nombre + guard)
            $role = Role::where('name', $data['role_name'])
                ->where('guard_name', $data['guard_name'])
                ->lockForUpdate() // 🔒 evita race conditions
                ->firstOrFail();

            // 2️⃣ Obtener SOLO permisos del mismo guard
            $permissions = Permission::whereIn('name', $data['permissions'])
                ->where('guard_name', $data['guard_name'])
                ->get();

            $user = Auth::user() ?? $request->user();

            // 3️⃣ Validación anti-hack (la muralla real)
            if ($permissions->count() !== count($data['permissions'])) {

                logger('🚨 Intento de asignación inválida de permisos', [
                    'auth_user_id' => $user?->id,
                    'role'         => $role->name,
                    'guard'        => $role->guard_name,
                    'requested'    => $data['permissions'],
                ]);

                abort(403, 'Permisos inválidos para este rol');
            }

            $role->givePermissionTo($permissions);

            return response()->json([
                'message'     => 'Permisos asignados correctamente',
                'role'        => $role->name,
                'guard'       => $role->guard_name,
                'permissions' => $permissions->pluck('name'),
            ]);
        });
    }

    /**
     * Asignar rol a un usuario (por guard)
     */
    public function assignRoleToUser(Request $request)
    {
        $data = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'role_name'  => 'required|string',
            'guard_name' => 'required|string',
        ]);

        $user = User::findOrFail($data['user_id']);

        $role = Role::where('name', $data['role_name'])
            ->where('guard_name', $data['guard_name'])
            ->firstOrFail();

        // 🔑 Aquí está la magia
        $user->syncRoles([$role]);

        return response()->json([
            'message' => 'Rol asignado correctamente',
            'user_id' => $user->id,
            'role'    => $role->name,
            'guard'   => $role->guard_name,
        ]);
    }


    public function getRoles()
    {
        $roles = Role::where('guard_name', 'rubro')
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);

        return response()->json([
            'guard' => 'rubro',
            'roles' => $roles,
        ]);
    }

    public function getPermissions()
    {
        $permissions = Permission::where('guard_name', 'rubro')
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);

        return response()->json([
            'guard'       => 'rubro',
            'permissions' => $permissions,
        ]);
    }

    public function getPermissionsByRole($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return response()->json($role->permissions);
    }

    public function getRolesByUser($userId)
    {
        $user = User::findOrFail($userId);

        $roles = $user->getRoleNames();

        return response()->json([
            'ok' => true,
            'data' => $roles
        ]);
    }
}
