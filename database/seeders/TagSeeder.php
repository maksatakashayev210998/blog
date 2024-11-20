<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Запуск сида.
     *
     * @return void
     */
    public function run()
    {
        Tag::create(['name' => 'Laravel']);
        Tag::create(['name' => 'PHP']);
        Tag::create(['name' => 'JavaScript']);
        Tag::create(['name' => 'React']);
        Tag::create(['name' => 'Vue']);
        Tag::create(['name' => 'Node.js']);
        Tag::create(['name' => 'HealthTech']);
        Tag::create(['name' => 'AI']);
    }
}
