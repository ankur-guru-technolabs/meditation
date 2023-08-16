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
        'duration',
        'is_featured',
    ];

    protected $appends = ['thumbnail_image_url','video_url'];

    // ACCESSOR

    public function getThumbnailImageUrlAttribute()
    {
        if($this->image->type == 'video_thumbnail_image'){
            return asset('/video/' . $this->image->file_name);
        }
        return null;
    }
   
    public function getVideoUrlAttribute()
    {
        if($this->video->type == 'video'){
            return asset('/video/' . $this->video->file_name);
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
