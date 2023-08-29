<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category_id',
        'duration',
        'is_featured',
        'unique_id',
        'can_view_free_user'
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

    public function userBookmarks()
    {
        $user = Auth::guard('api')->user();
        if($user) {
            return $this->hasMany(Bookmark::class, 'video_id')->where('user_id',$user->id);
        }
        return $this->hasMany(Bookmark::class, 'video_id')->where('id',0);
    }
}
