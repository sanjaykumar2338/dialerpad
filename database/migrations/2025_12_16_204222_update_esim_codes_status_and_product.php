<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('esim_codes', function (Blueprint $table) {
            $table->string('product_id')->nullable()->after('esim_type_id');
            $table->string('status', 32)->default('unused')->change();
            $table->timestamp('used_at')->nullable()->after('status');
        });

        DB::table('esim_codes')
            ->join('esim_types', 'esim_codes.esim_type_id', '=', 'esim_types.id')
            ->update([
                'esim_codes.product_id' => DB::raw('esim_types.product_id'),
            ]);

        DB::table('esim_codes')->where('status', 'active')->update(['status' => 'unused']);
    }

    public function down(): void
    {
        Schema::table('esim_codes', function (Blueprint $table) {
            $table->dropColumn(['product_id', 'used_at']);
            $table->enum('status', ['active', 'disabled'])->default('active')->change();
        });
    }
};
