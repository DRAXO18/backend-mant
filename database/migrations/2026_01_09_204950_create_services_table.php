<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 03_create_services_tables.php
 * Depends on: vehicles, clients
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();

            $table->string('name', 120)->unique();
            $table->text('description')->nullable();
            $table->string('category', 60)->nullable()->index();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete(); // ✅ sin index

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete(); // ✅ sin index

            $table->tinyInteger('status')
                ->default(1)
                ->index()
                ->comment('0=inactive,1=active');

            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('services', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('service_type_id');
            $table->unsignedBigInteger('client_id');

            $table->dateTime('service_date');

            // 👇 elimina la columna inválida
            // $table->unsignedInteger('   ')->index(); ❌ ELIMINADO

            $table->tinyInteger('status')
                ->default(1)
                ->comment('0=draft,1=open,2=in_progress,3=completed,4=cancelled');

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete(); // ✅

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete(); // ✅

            $table->timestamps();
            $table->softDeletes();

            // Índices reales (correctos)
            $table->index(['vehicle_id', 'service_date'], 'idx_services_vehicle_date');
            $table->index(['client_id', 'service_date'], 'idx_services_client_date');
            $table->index(['service_type_id', 'service_date'], 'idx_services_type_date');

            // Foreign keys explícitas
            $table->foreign('vehicle_id', 'fk_services_vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('service_type_id', 'fk_services_service_type_id')
                ->references('id')
                ->on('service_types')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('client_id', 'fk_services_client_id')
                ->references('id')
                ->on('clients')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::create('service_details', function (Blueprint $table) {
            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->primary();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete(); // ✅

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete(); // ✅

            $table->text('observations')->nullable();
            $table->text('recommendation')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_details');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_types');
    }
};
