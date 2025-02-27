<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoBid extends Model
{
    use HasFactory;

    protected $fillable = [
        "max_bid",
        "item_id",
        "user_id",
        "date_time"
    ];

    public function auctionItem(){
        return $this->belongsTo(Item::class);
    }

    public function bid(){
        return $this->belongsTo(Bid::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
