<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('technicians', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('specialization')->nullable();
            $table->unsignedSmallInteger('experience_years')->default(0);

            $table->boolean('verified')->default(false);

            $table->tinyInteger('status')
                ->default(1)
                ->index()
                ->comment('0=inactive,1=active,2=suspended,3=banned,4=deleted');

            $table->timestamps();

            $table->unique(['user_id', 'company_id']);

            $table->index('company_id');
            $table->index('verified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technicians');
    }
};
