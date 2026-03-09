<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {

            $table->foreignId('technician_id')
                ->constrained('technicians')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index(
                ['technician_id', 'service_date'],
                'idx_services_technician_date'
            );
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {

            $table->dropForeign(['technician_id']);
            $table->dropIndex('idx_services_technician_date');
            $table->dropColumn('technician_id');
        });
    }
};
