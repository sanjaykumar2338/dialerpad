<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('call_cards', 'account_id')) {
            Schema::table('call_cards', function (Blueprint $table) {
                $table->foreignId('account_id')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('call_cards', 'activated_at')) {
            Schema::table('call_cards', function (Blueprint $table) {
                $table->timestamp('activated_at')->nullable()->after('status');
            });
        }

        Schema::table('call_cards', function (Blueprint $table) {
            $table->index('status');
        });

        if (! Schema::hasColumn('esim_codes', 'account_id')) {
            Schema::table('esim_codes', function (Blueprint $table) {
                $table->foreignId('account_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('esim_codes', 'batch_id')) {
            Schema::table('esim_codes', function (Blueprint $table) {
                $table->uuid('batch_id')->nullable()->after('account_id')->index();
            });
        }

        Schema::table('esim_codes', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('esim_codes', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        if (Schema::hasColumn('esim_codes', 'batch_id')) {
            Schema::table('esim_codes', function (Blueprint $table) {
                $table->dropColumn('batch_id');
            });
        }

        if (Schema::hasColumn('esim_codes', 'account_id')) {
            Schema::table('esim_codes', function (Blueprint $table) {
                $table->dropConstrainedForeignId('account_id');
            });
        }

        Schema::table('call_cards', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        if (Schema::hasColumn('call_cards', 'activated_at')) {
            Schema::table('call_cards', function (Blueprint $table) {
                $table->dropColumn('activated_at');
            });
        }

        if (Schema::hasColumn('call_cards', 'account_id')) {
            Schema::table('call_cards', function (Blueprint $table) {
                $table->dropConstrainedForeignId('account_id');
            });
        }
    }
};
