<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'price',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function subscriptionOrder(){
        return $this->belongsTo(Subscription::class,'subscription_id');
    }
}
