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
            'name' => 'Superadmin',
            'guard_name' => 'web'
        ]);

        // permisos de vehicles
        $permissions = [

            // users.admin
            'users.admin.view',
            'users.admin.create',
            'users.admin.update',
            'users.admin.delete',

            // users.client
            'users.client.view',
            'users.client.create',
            'users.client.update',
            'users.client.delete',

            // users.owner
            'users.owner.view',
            'users.owner.create',
            'users.owner.update',
            'users.owner.delete',

            // vehicles
            'vehicles.view',
            'vehicles.create',
            'vehicles.update',
            'vehicles.delete',

            // services
            'services.view',
            'services.create',
            'services.update',
            'services.delete',

            // services.details
            'services.details.create',

            // service-types
            'service-types.view',
            'service-types.create',
            'service-types.update',
            'service-types.delete',

            // products
            'products.view',
            'products.create',
            'products.update',
            'products.delete',

            // roles
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',

            // permissions
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
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
