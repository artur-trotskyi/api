<?php

namespace Database\Factories;

use App\Constants\AppConstants;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'tags' => collect(AppConstants::TAGS)
                ->random(2)->values()->all(),
        ];
    }
}
