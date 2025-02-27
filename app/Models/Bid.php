<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = [
        "bid",
        "item_id",
        "user_id",
        "date_time"
    ];

    public function item(){
        return $this->belongsTo(Item::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    protected static function boot(){
        parent::boot();

        static::created(function ($bid){
            $bid->item->highest_bid = $bid->bid;
            $bid->item->save();
        });
    }
}
