<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_addresses', function (Blueprint $table) {
            $table->id();

            // Relación con clients
            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            // Relación con ubigeo
            $table->foreignId('ubigeo_id')
                ->constrained('ubigeo')
                ->restrictOnDelete();

            // Dirección exacta
            $table->string('address_line')->nullable();   // Calle, número, mz, lote
            $table->string('reference')->nullable();      // "Frente a la bodega"

            // Geolocalización (opcional pero recomendado)
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();

            // Dirección principal
            $table->boolean('is_primary')->default(false);

            // Estado general
            $table->enum('status', ['active', 'inactive'])
                ->default('active');

            $table->timestamps();

            // Índices
            $table->index('client_id');
            $table->index('ubigeo_id');
            $table->index('is_primary');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_addresses');
    }
};
