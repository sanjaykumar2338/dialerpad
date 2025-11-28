<?php

namespace Database\Seeders;

use App\Models\CallCard;
use App\Models\User;
use Illuminate\Database\Seeder;

class CallCardSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('is_admin', true)->first() ?? User::first();

        if (!$admin) {
            $admin = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'is_admin' => true,
            ]);
        }

        $cards = [
            [
                'name' => 'Accra Business',
                'prefix' => '00233',
                'total_minutes' => 300,
                'used_minutes' => 45,
                'notes' => 'Default Ghana line',
            ],
            [
                'name' => 'Paris Travel',
                'prefix' => '0033',
                'total_minutes' => 120,
                'used_minutes' => 90,
                'notes' => 'Short term roaming',
            ],
            [
                'name' => 'Lagos VIP',
                'prefix' => '00234',
                'total_minutes' => 600,
                'used_minutes' => 0,
                'notes' => 'VIP priority customers',
            ],
            [
                'name' => 'Abidjan Promo',
                'prefix' => '00225',
                'total_minutes' => 60,
                'used_minutes' => 60,
                'notes' => 'Sample expired card',
                'status' => 'expired',
            ],
        ];

        foreach ($cards as $card) {
            CallCard::factory()
                ->for($admin, 'creator')
                ->state(function () use ($card) {
                    return [
                        'name' => $card['name'],
                        'prefix' => $card['prefix'],
                        'total_minutes' => $card['total_minutes'],
                        'used_minutes' => $card['used_minutes'],
                        'status' => $card['status'] ?? 'active',
                        'notes' => $card['notes'],
                    ];
                })
                ->create();
        }
    }
}
