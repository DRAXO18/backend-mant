<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // Acción administrativa
            $table->enum('action', [
                'approved',
                'rejected',
                'suspended'
            ]);

            // Motivo u observación (obligatorio en reject)
            $table->text('reason')->nullable();

            // Usuario RUBRO que ejecutó la acción
            $table->foreignId('performed_by')
                ->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_reviews');
    }
};
