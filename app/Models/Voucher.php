<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_id',
        'title',
        'enthusiast',
        'address_enthusiast',
        'end_date',
        'description',
        'short_description',
        'image_path',
        'published',
    ];

    protected static function booted(){
        static::created(function($voucher) {
            Item::create([
                'auction_item_id' => $voucher->id,
                'itemable_id' => $voucher->id,
                'itemable_type' => self::class,
                'type' => 'voucher',
                'auction_id' => $voucher->auction_id
            ]);
        });
        static::deleting(function ($voucher) {
            $voucher->items()->each(function ($item) {
                $item->delete();
            });

            $voucher->images()->each(function ($image) {
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
