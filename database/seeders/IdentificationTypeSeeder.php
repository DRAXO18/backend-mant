<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IdentificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \App\Models\IdentificationType::insert([
            ['name' => 'DNI', 'code' => 'dni'],
            ['name' => 'Carnet de Extranjería', 'code' => 'ce'],
            ['name' => 'Pasaporte', 'code' => 'passport'],
            ['name' => 'RUC', 'code' => 'ruc'],
        ]);
    }
}
