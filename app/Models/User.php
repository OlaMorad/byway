<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Notifications\CustomPasswordReset;

use Laravel\Scout\Searchable;


use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, Searchable,  SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $guarded = ['id'];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomPasswordReset($token));
    }

    // // Accessors for links
    // public function getTwitterLinkAttribute($value)
    // {
    //     return $value ?? 'https://twitter.com';
    // }

    // public function getLinkedInLinkAttribute($value)
    // {
    //     return $value ?? 'https://www.linkedin.com';
    // }

    // public function getYoutubeLinkAttribute($value)
    // {
    //     return $value ?? 'https://www.youtube.com';
    // }

    // public function getFacebookLinkAttribute($value)
    // {
    //     return $value ?? 'https://www.facebook.com';
    // }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_code'
    ];
    protected $casts = [
        'total_earnings' => 'float',
    ];

    // Optional helper methods
    public function isLearner()
    {
        return $this->role === 'learner';
    }
    public function isTeacher()
    {
        return $this->role === 'teacher';
    }
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'deletion_requested_at' => 'datetime',
        ];
    }
    // دالة مخصصة بترجع الاسم كامل
    public function fullName()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favoriteCourses()
    {
        return $this->belongsToMany(Course::class, 'favorites')->withTimestamps();
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }


    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function instructorProfile()
    {
        return $this->hasOne(InstructorProfile::class, 'user_id');
    }


    public function carts()
    {
        return $this->hasMany(Cart::class);
    }





    // Check if deletion is pending
    public function isPendingDeletion()
    {
        return $this->status === 'pending_deletion';
    }


    // Check if within cancellation window (14 days)
    public function canCancelDeletion()
    {
        if (!$this->isPendingDeletion()) return false;

        return $this->deletion_requested_at->addDays(14)->isFuture();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function toSearchableArray()
    {
        return [
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
          //  'full_name'  => $this->fullName(),
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'nationality' => $this->nationality,
        ];
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments', 'learner_id', 'course_id');
    }





    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'learner_id');
    }
}
