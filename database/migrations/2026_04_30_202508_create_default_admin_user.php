<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $values = [
            'name' => 'Admin',
            'email' => 'adminz@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('adminm'),
            'is_admin' => true,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'status')) {
            $values['status'] = User::STATUS_ACTIVE;
        }

        if (Schema::hasColumn('users', 'role')) {
            $values['role'] = User::ROLE_DISTRIBUTOR;
        }

        DB::table('users')->updateOrInsert(
            ['email' => 'adminz@gmail.com'],
            array_merge($values, ['created_at' => now()])
        );
    }

    public function down(): void
    {
        // Intentionally left blank so rolling back never deletes user records.
    }
};
