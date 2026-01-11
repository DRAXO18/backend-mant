<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 02_create_vehicles_tables.php
 * Depends on: owners
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('owner_id');

            $table->foreign('owner_id', 'fk_vehicles_owner_id')
                ->references('id')
                ->on('owners')
                ->restrictOnDelete()
                ->cascadeOnUpdate();


            $table->string('plate_number', 15)->unique();
            $table->string('brand', 80)->index();
            $table->string('model', 80)->index();
            $table->unsignedInteger('current_mileage')->default(0)->index();

            $table->tinyInteger('status')
                ->default(1)
                ->index()
                ->comment('0=inactive,1=active');

            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('vehicle_details', function (Blueprint $table) {
            // 1-1 satellite, keep it slim
            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->primary();

            $table->string('usage_type', 20)->nullable()->index(); // PRIVATE / PUBLIC
            $table->boolean('has_gnv')->default(false)->index();
            $table->boolean('has_glp')->default(false)->index();

            $table->unsignedInteger('weekly_mileage')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['usage_type', 'has_gnv', 'has_glp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_details');
        Schema::dropIfExists('vehicles');
    }
};
