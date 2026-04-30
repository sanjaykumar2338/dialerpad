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

        $productIds = DB::table('esim_types')->pluck('product_id', 'id');

        DB::table('esim_codes')
            ->select(['id', 'esim_type_id'])
            ->orderBy('id')
            ->cursor()
            ->each(function ($code) use ($productIds): void {
                DB::table('esim_codes')
                    ->where('id', $code->id)
                    ->update([
                        'product_id' => $productIds[$code->esim_type_id] ?? null,
                    ]);
            });

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
