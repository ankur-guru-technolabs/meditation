<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Pdf extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category_id',
        'unique_id',
        'can_view_free_user',
        'pdf_type'
    ];

    protected $appends = ['thumbnail_image_url','pdf_url'];

    protected $casts = [
        'category_id'=> 'int',
    ];
    
    // ACCESSOR

    public function getThumbnailImageUrlAttribute()
    {
        if(isset($this->image->type) && $this->image->type == 'pdf_thumbnail_image'){
            return asset('/pdf/' . $this->image->file_name);
        }
        return null;
    }
   
    public function getPdfUrlAttribute()
    {
        if($this->pdf->type == 'pdf'){
            return asset('/pdf/' . $this->pdf->file_name);
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
        return $this->hasOne(Image::class,'type_id','id')->where('type','pdf_thumbnail_image');
    }

    public function pdf()
    {
        return $this->hasOne(Image::class,'type_id','id')->where('type','pdf');
    }

}
