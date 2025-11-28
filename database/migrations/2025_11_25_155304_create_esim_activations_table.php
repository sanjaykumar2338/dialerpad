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
        Schema::create('esim_activations', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->foreignId('esim_type_id')->constrained('esim_types')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->json('provider_response_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('esim_activations');
    }
};
