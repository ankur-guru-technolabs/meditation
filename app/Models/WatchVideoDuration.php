<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchVideoDuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'video_id',
        'category_id',
        'duration',
        'play_date'
    ];
}
