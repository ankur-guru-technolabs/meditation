<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'button_title'
    ];

    // RELATIONSHIPS

    public function image()
    {
        return $this->hasOne(Image::class,'type_id','id')->where('type','category_image');
    }
}
