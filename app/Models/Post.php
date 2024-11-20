<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'cover_image',
        'status',
        'author_id'
    ];

    // Связь с автором (пользователем)
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // Связь с категориями (многие-ко-многим)
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    // Связь с тегами (многие-ко-многим)
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
