<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('esim_types', function (Blueprint $table) {
            $table->string('product_id')->nullable()->after('name');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('esim_types', function (Blueprint $table) {
            $table->dropColumn(['product_id', 'status']);
        });
    }
};
