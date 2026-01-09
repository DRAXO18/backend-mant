<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'prueba User',
            'email' => 'prueba@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $this->call([
            IdentificationTypeSeeder::class,
        ]);
    }
}
