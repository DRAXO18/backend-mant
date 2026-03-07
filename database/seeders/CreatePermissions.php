<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class CreatePermissions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        foreach (config('system_permissions') as $module => $actions) {

            foreach ($actions as $action) {

                Permission::firstOrCreate([
                    'name' => "$module.$action"
                ]);
            }
        }
    }
}
