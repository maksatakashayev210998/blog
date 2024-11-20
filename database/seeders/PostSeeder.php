<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run()
    {
        $user = User::inRandomOrder()->first();

        $categories = Category::inRandomOrder()->take(2)->pluck('id');
        $tags = Tag::inRandomOrder()->take(3)->pluck('id');

        $post = Post::create([
            'title' => 'First Post',
            'content' => 'First Post content.',
            'status' => 'draft',
            'author_id' => $user->id,
        ]);

        $post->categories()->attach($categories);
        $post->tags()->attach($tags);

        \App\Models\Post::factory(10)->create()->each(function ($post) {
            $categories = Category::inRandomOrder()->take(2)->pluck('id');
            $tags = Tag::inRandomOrder()->take(3)->pluck('id');

            $post->categories()->attach($categories);
            $post->tags()->attach($tags);
        });
    }
}
