<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pigeon extends Model
{
    use HasFactory;

    protected $fillable = [
        "auction_id",
        "title",
        "short_description",
        "description",
        "ring_number",
        "color",
        "sex",
        "end_date",
        "name",
        "published"
    ];
    protected static function booted(){
        static::created(function($pigeon) {
            Item::create([
                'auction_item_id' => $pigeon->id,
                'itemable_id' => $pigeon->id,
                'itemable_type' => self::class,
                'type' => 'pigeon',
                'auction_id' => $pigeon->auction_id 
            ]);
        });

        static::deleting(function ($pigeon) {
            $pigeon->items()->each(function ($item) {
                $item->delete();
            });

            $pigeon->images()->each(function ($image) {
                $image->delete();
            });
        });
    }

    public function items(){
        return $this->morphMany(Item::class,'itemable');
    } 
    public function auction(){
        return $this->belongsTo(Auction::class);
    }

    public function auctionImage(){
        return $this->hasOne(AuctionItemImage::class);
    }
    public function images()
    {
        return $this->morphMany(AuctionItemImage::class, 'imageable');
    }


}
