<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['video', 'image']);

        return [
            'type' => $type,
            'title' => fake()->sentence(4),
            'description' => fake()->optional(0.7)->paragraph(),
            'media_path' => $type === 'image' ? 'images/sample.jpg' : 'videos/sample.mp4',
            'thumbnail_path' => null,
            'status' => fake()->randomElement(['draft', 'published']),
            'sort_order' => fake()->numberBetween(0, 100),
            'views_count' => fake()->numberBetween(0, 10000),
            'is_featured' => fake()->boolean(20),
            'published_at' => fake()->optional(0.6)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function video(): static
    {
        return $this->state(fn () => [
            'type' => 'video',
            'media_path' => 'videos/sample.mp4',
        ]);
    }

    public function image(): static
    {
        return $this->state(fn () => [
            'type' => 'image',
            'media_path' => 'images/sample.jpg',
        ]);
    }
}
