<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence;

        return [
            'user_id' => fn () => User::factory()->create()->id,
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $this->faker->paragraph,
            'published_at' => $this->faker->dateTimeBetween('-1 week', '+1 week'),
        ];
    }
}
