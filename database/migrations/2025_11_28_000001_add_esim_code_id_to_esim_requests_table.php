<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('esim_requests', function (Blueprint $table) {
            $table->foreignId('esim_code_id')
                ->nullable()
                ->after('id')
                ->constrained('esim_codes');
        });
    }

    public function down(): void
    {
        Schema::table('esim_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('esim_code_id');
        });
    }
};
