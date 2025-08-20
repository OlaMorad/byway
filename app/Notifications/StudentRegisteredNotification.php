<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StudentRegisteredNotification extends Notification
{
    // use Queueable;

    public function __construct(public array $payload) {}

   
    public function via(object $notifiable): array
    {
        return ['database'];
    }

  
    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => 'New student registration',
            'message' => "{$this->payload['student_name']} enrolled in {$this->payload['course_title']}",
            'meta'    => [
                'course_id'  => $this->payload['course_id'] ?? null,
                'student_id' => $this->payload['student_id'] ?? null,
            ],
        ];
    }

  
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
