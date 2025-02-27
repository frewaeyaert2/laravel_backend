<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        "auction_type_id",
        "name",
        "description",
        "short_description",
        "start_date",
        "end_date",
        "image_path",
        "active",
        "published"
    ];

    protected $attributes = [
        'active' => false,
    ];

    public function pigeons(){
        return $this->hasMany(Pigeon::class);
    }

    public function vouchers(){
        return $this->hasMany(Voucher::class);
    }

    public function items(){
        return $this->hasMany(Item::class);
    }

    public function auctionType(){
        return $this->belongsTo(AuctionType::class);
    }
    public function images()
    {
        return $this->morphMany(AuctionItemImage::class, 'imageable');
    }
}
