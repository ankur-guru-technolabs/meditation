<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category_id',
        'duration'
    ];

    protected $appends = ['thumbnail_image_url'];

    // ACCESSOR

    public function getThumbnailImageUrlAttribute()
    {
        if($this->type == 'video_thumbnail_image'){
            return asset('/video_thumbnail_image/' . $this->file_name);
        }
        return null;
    }

    // RELATIONSHIP

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function image()
    {
        return $this->hasOne(Image::class,'type_id','id')->where('type','video_thumbnail_image');
    }

    public function video()
    {
        return $this->hasOne(Image::class,'type_id','id')->where('type','video');
    }
}
