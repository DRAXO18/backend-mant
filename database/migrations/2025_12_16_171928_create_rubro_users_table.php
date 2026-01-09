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
        Schema::create('rubro_users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('username')->unique();

            // campos legales / auditoría
            $table->string('position')->nullable();
            $table->timestamp('appointed_at')->nullable();

            $table->tinyInteger('status')
                ->default(1)
                ->index()
                ->comment('0=inactive,1=active,2=suspended,3=revoked');

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubro_users');
    }
};
