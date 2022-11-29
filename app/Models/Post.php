<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'posts';
    protected $fillable = ['title','content', 'image', 'pinned', 'user_id'];

    public function tags(){
        return $this->belongsToMany(Tag::class, 'tag_post');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
