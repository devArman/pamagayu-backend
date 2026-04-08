<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        Post::factory()->count(3)->published()->image()->create();
        Post::factory()->count(3)->published()->gallery(4)->create();
        Post::factory()->count(5)->published()->video()->create();
        Post::factory()->count(2)->draft()->image()->create();
        Post::factory()->count(1)->draft()->gallery(6)->create();
        Post::factory()->count(2)->draft()->video()->create();
    }
}
