<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        return [
            'organization_id' => Organization::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'venue' => $this->faker->address,
            'date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'price' => $this->faker->randomFloat(2, 0, 1000),
            'max_attendees' => $this->faker->numberBetween(10, 500),
            'status' => $this->faker->randomElement(['draft', 'published', 'cancelled']),
        ];
    }
}
