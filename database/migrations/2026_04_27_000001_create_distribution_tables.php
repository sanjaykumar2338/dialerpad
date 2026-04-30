<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('batch_requests')) {
            Schema::create('batch_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_id')->constrained('users')->cascadeOnDelete();
                $table->string('product_type', 32)->index();
                $table->unsignedInteger('quantity');
                $table->string('status', 32)->default('pending')->index();
                $table->text('notes')->nullable();
                $table->json('generation_settings')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->string('delivery_document_path')->nullable();
                $table->timestamps();

                $table->index(['account_id', 'status']);
                $table->index(['account_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('batches')) {
            Schema::create('batches', function (Blueprint $table) {
                $table->id();
                $table->uuid('batch_id')->unique();
                $table->foreignId('account_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('batch_request_id')->nullable()->unique()->constrained('batch_requests')->nullOnDelete();
                $table->string('product_type', 32)->index();
                $table->string('status', 32)->default('generated')->index();
                $table->unsignedInteger('total_cards')->default(0);
                $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->string('delivery_document_path')->nullable();
                $table->timestamps();

                $table->index('account_id');
                $table->index(['account_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->uuid('batch_id')->nullable()->index();
                $table->foreignId('batch_request_id')->nullable()->constrained('batch_requests')->nullOnDelete();
                $table->string('event', 64)->index();
                $table->string('description');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['account_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('batch_requests');
    }
};
