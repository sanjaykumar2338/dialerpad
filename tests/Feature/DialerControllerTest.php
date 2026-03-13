<?php

namespace Tests\Feature;

use App\Models\CallCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DialerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_call_accepts_long_dialed_numbers(): void
    {
        $card = CallCard::factory()->create([
            'prefix' => '223',
            'status' => 'active',
            'total_minutes' => 60,
            'used_minutes' => 0,
        ]);

        $dialedNumber = '223' . str_repeat('7', 37);

        $response = $this->postJson(route('dialer.start', ['uuid' => $card->uuid]), [
            'dialed_number' => $dialedNumber,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('call_sessions', [
            'call_card_id' => $card->id,
            'dialed_number' => $dialedNumber,
            'full_number' => $dialedNumber,
            'status' => 'started',
        ]);
    }
}
