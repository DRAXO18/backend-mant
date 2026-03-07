<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsToRoleTest extends Seeder
{
    public function run(): void
    {
        // crear rol admin si no existe
        $role = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web'
        ]);

        // permisos de vehicles
        $permissions = [
            'vehicles.view',
            'vehicles.create',
            'vehicles.update',
            'vehicles.delete'
        ];

        foreach ($permissions as $permission) {
            $perm = Permission::where('name', $permission)->first();

            if ($perm) {
                $role->givePermissionTo($perm);
            }
        }

        // buscar usuario
        $user = User::find(1);

        if ($user) {
            $user->assignRole($role);
        }
    }
}