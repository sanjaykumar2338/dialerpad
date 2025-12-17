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
        Schema::table('call_sessions', function (Blueprint $table) {
            $table->enum('status', ['started', 'completed', 'failed', 'cancelled', 'ended'])
                ->default('started')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_sessions', function (Blueprint $table) {
            $table->enum('status', ['started', 'completed', 'failed', 'cancelled', 'ended'])
                ->default('started')
                ->change();
        });
    }
};
