<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable , HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password', 'role','verification_code'];

    /*protected $guarded = [
        'id'
    ];*/

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

    // Optional helper methods
    public function isLearner() { return $this->role === 'learner'; }
    public function isTeacher() { return $this->role === 'teacher'; }
    public function isAdmin()   { return $this->role === 'admin'; }
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
        ];
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    
    public function favoriteCourses()
    {
        return $this->belongsToMany(Course::class, 'favorites')->withTimestamps();
    }
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

        public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }
}
