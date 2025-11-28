<?php

namespace Database\Factories;

use App\Models\CallCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CallCard>
 */
class CallCardFactory extends Factory
{
    protected $model = CallCard::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->words(2, true) . ' Card',
            'prefix' => '00' . $this->faker->numerify('###'),
            'total_minutes' => $this->faker->numberBetween(60, 600),
            'used_minutes' => 0,
            'status' => 'active',
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
