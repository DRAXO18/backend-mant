<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // identification_types
        Schema::create('identification_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // dni, ce, passport
            $table->timestamps();

            $table->index('name');
            $table->index('code');
        });

        // user_identifications
        Schema::create('user_identifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('identification_type_id')
                ->constrained('identification_types')
                ->cascadeOnDelete();

            $table->string('number_hash');
            $table->text('number_encrypted');

            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('expires_at');

            $table->unique(
                ['identification_type_id', 'number_hash'],
                'unique_identification_number'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_identifications');
        Schema::dropIfExists('identification_types');
    }
};
