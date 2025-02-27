<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $fillable = ['auction_item_id', 'itemable_type', 'itemable_id', 'type', 'auction_id'];

    public function itemable()
    {
        return $this->morphTo();
    }

    public function bids(){
        return $this->hasMany(Bid::class);
    }

    // link between item and user
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getHighestBidAttribute(){
        if($this->bids()->exists()){
            return $this->bids()->max('bid');
        } else {
            return 0;
        }
    }
}
