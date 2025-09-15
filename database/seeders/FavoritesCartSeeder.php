<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cart;
use App\Models\Favorite;

class FavoritesCartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 1; // From UserSeeder

        // Favorite: Web Dev + UI/UX
        Favorite::firstOrCreate(['user_id' => $userId, 'course_id' => 1]);
        Favorite::firstOrCreate(['user_id' => $userId, 'course_id' => 2]);

        // Cart: Python + Mobile App
        Cart::firstOrCreate(['user_id' => $userId, 'course_id' => 3]);
        Cart::firstOrCreate(['user_id' => $userId, 'course_id' => 4]);
    }
}
