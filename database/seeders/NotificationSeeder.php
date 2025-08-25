<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Notifications\Notification;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      // Find a specific learner
        $user = User::where('email', 'learner@example.com')->first();

        // Or get a user with role 'learner'
        if (!$user) {
            $user = User::where('role', 'learner')->first();
        }

        // Fallback: create a test learner if none exists
        if (!$user) {
            $user = User::create([
                'name' => 'Test Learner',
                'email' => 'learner@example.com',
                'password' => Hash::make('password123'),
                'role' => 'learner',
                'status' => 'Active',
            ]);
        }

        // Send a test notification
        $user->notify(new class extends Notification {
            public function via($notifiable)
            {
                return ['database'];
            }

            public function toDatabase($notifiable)
            {
                return [
                    'title' => 'Welcome to Byway!',
                    'message' => 'You’ve successfully enrolled in Web Development Basics.',
                    'url' => '/learner/courses/1'
                ];
            }
        });

        echo "✅ Notification sent to {$user->email}\n";
    }
}
