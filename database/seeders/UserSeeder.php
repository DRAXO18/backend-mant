<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuarios fake
        // User::factory()->count(5)->create();

        // Usuario controlado (para Postman)
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@demo.com',
            'password' => bcrypt('123456'),
            'status' => 1,
        ]);
    }
}
