<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MultitenantTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |----------------------------------
            | Companies
            |----------------------------------
            */

            $companyA = Company::create([
                'name' => 'Taller Norte'
            ]);

            $companyB = Company::create([
                'name' => 'Taller Sur'
            ]);

            /*
            |----------------------------------
            | Admin Users
            |----------------------------------
            */

            $adminA = User::create([
                'name' => 'Admin Norte',
                'email' => 'admin1@test.com',
                'password' => Hash::make('password')
            ]);

            $adminB = User::create([
                'name' => 'Admin Sur',
                'email' => 'admin2@test.com',
                'password' => Hash::make('password')
            ]);

            /*
            |----------------------------------
            | Pivot company_user
            |----------------------------------
            */

            DB::table('company_user')->insert([
                [
                    'company_id' => $companyA->id,
                    'user_id' => $adminA->id
                ],
                [
                    'company_id' => $companyB->id,
                    'user_id' => $adminB->id
                ]
            ]);

            /*
            |----------------------------------
            | Role Admin
            |----------------------------------
            */

            $adminRole = Role::firstOrCreate([
                'id' => 1
            ], [
                'name' => 'Admin',
                'guard_name' => 'web'
            ]);

            /*
            |----------------------------------
            | Permissions
            |----------------------------------
            */

            $permissions = [
                'users.owner.view',
                'users.owner.create',
                'users.owner.update',
                'users.owner.delete'
            ];

            foreach ($permissions as $perm) {

                $permission = Permission::firstOrCreate([
                    'name' => $perm,
                    'guard_name' => 'web'
                ]);

                $adminRole->givePermissionTo($permission);
            }

            /*
            |----------------------------------
            | Assign role to admins
            |----------------------------------
            */

            $adminA->assignRole($adminRole);
            $adminB->assignRole($adminRole);

        });
    }
}