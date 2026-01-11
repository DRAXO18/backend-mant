<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 01_create_profiles_tables.php
 * Depends on: users table (Laravel default migration) + ubigeo table (already exists)
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate()
                ->unique();

            $table->foreignId('ubigeo_id')
                ->nullable()
                ->constrained('ubigeo')
                ->nullOnDelete()
                ->cascadeOnUpdate()
                ->index();


            $table->string('address')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id']);
        });

        Schema::create('owners', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate()
                ->unique();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id']);
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate()
                ->unique();

            $table->string('uid', 50)->unique(); // internal business UID like ADM-000123

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
        Schema::dropIfExists('owners');
        Schema::dropIfExists('clients');
    }
};
