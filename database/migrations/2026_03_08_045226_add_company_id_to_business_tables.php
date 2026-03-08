<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
