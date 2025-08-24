<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Course extends Model
{
    use HasFactory,Searchable;

    protected $fillable = [
        'title',
        'description',
        'video_url',
        'status',
        'price',
        'category_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favoredByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function instructor()
    {
    return $this->belongsTo(User::class, 'instructor_id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function toSearchableArray()
    {
        $array = [
            'id'             => $this->id,
            'title'          => $this->title,
            'instructor_name' => $this->user?->name,
            'category_name'  => $this->category?->name,
            'status'         => $this->status,
        ];

        return $array;
    }

}
