<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'movie_id',
        'rating',
        'comment',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    // Backwards compatibility with existing views that expect 'comment' and 'status'
    public function getCommentAttribute()
    {
        return $this->attributes['comment'] ?? null;
    }

    public function setCommentAttribute($value)
    {
        $this->attributes['comment'] = $value;
    }

    public function getStatusAttribute()
    {
        return $this->attributes['status'] ?? 'HIDDEN';
    }
}

