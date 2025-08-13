<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Review;
use App\Models\Category;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Factories\ReviewFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->sequence(['role' => 'learner'], ['role' => 'instructor'])->create();
        $this->call([
            CategorySeeder::class,
        ]);
        Course::factory(10)->create();
        Lesson::factory(50)->create();
        Review::factory(50)->create();
    }
}
