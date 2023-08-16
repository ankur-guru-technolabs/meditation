<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'video_id',
        'video_title',
    ];

    protected $casts = [
        'user_id'=> 'int',
        'video_id'=> 'int', 
    ];

    // RELATIONSHIPS

    public function video()
    {
        return $this->hasOne(Video::class,'id','video_id');
    }
}
