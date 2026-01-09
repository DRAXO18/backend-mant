<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | companies
        |--------------------------------------------------------------------------
        */
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('ruc')->unique();

            // ubicación exacta (NO address string)
            $table->foreignId('ubigeo_id')
                ->constrained('ubigeo')
                ->restrictOnDelete();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->tinyInteger('status')
                ->default(1)
                ->index()
                ->comment('0=inactive,1=active,2=suspended,3=deleted');

            $table->tinyInteger('approval_status')
                ->default(0)
                ->index()
                ->comment('0=pending,1=approved,2=rejected,3=suspended');

            $table->timestamps();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->index('ubigeo_id');
        });

        /*
        |--------------------------------------------------------------------------
        | company_users (pertenencia usuario ↔ empresa)
        |--------------------------------------------------------------------------
        */
        Schema::create('company_users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->tinyInteger('status')
                ->default(1)
                ->index()
                ->comment('0=inactive,1=active,2=suspended,3=banned,4=deleted');

            $table->timestamps();

            // un usuario solo una vez por empresa
            $table->unique(['company_id', 'user_id']);
        });

        /*
        |--------------------------------------------------------------------------
        | company_roles (roles internos por empresa)
        |--------------------------------------------------------------------------
        */
        Schema::create('company_roles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('name'); // Admin, Técnico, Colaborador
            $table->string('slug'); // admin, technician, collaborator

            $table->timestamps();

            // un rol único por empresa
            $table->unique(['company_id', 'slug']);
        });

        /*
        |--------------------------------------------------------------------------
        | company_role_user (pivot roles ↔ usuarios)
        |--------------------------------------------------------------------------
        */
        Schema::create('company_role_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_user_id')
                ->constrained('company_users')
                ->cascadeOnDelete();

            $table->foreignId('company_role_id')
                ->constrained('company_roles')
                ->cascadeOnDelete();

            $table->timestamps();

            // evita roles duplicados al mismo usuario
            $table->unique(['company_user_id', 'company_role_id']);
        });
    }

    public function down(): void
    {
        // orden inverso por FK
        Schema::dropIfExists('company_role_user');
        Schema::dropIfExists('company_roles');
        Schema::dropIfExists('company_users');
        Schema::dropIfExists('companies');
    }
};
