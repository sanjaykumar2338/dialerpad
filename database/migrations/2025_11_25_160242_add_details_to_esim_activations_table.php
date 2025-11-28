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
        Schema::table('esim_activations', function (Blueprint $table) {
            $table->dropForeign(['esim_type_id']);
        });

        Schema::table('esim_activations', function (Blueprint $table) {
            $table->foreignId('esim_type_id')->nullable()->change();
        });

        Schema::table('esim_activations', function (Blueprint $table) {
            $table->foreign('esim_type_id')->references('id')->on('esim_types')->nullOnDelete();
            $table->string('full_name')->after('email');
            $table->string('country_or_device')->nullable()->after('full_name');
            $table->string('request_uuid')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('esim_activations', function (Blueprint $table) {
            $table->dropForeign(['esim_type_id']);
        });

        Schema::table('esim_activations', function (Blueprint $table) {
            $table->foreignId('esim_type_id')->nullable(false)->change();
            $table->foreign('esim_type_id')->references('id')->on('esim_types')->cascadeOnDelete();
            $table->dropColumn(['full_name', 'country_or_device', 'request_uuid']);
        });
    }
};
