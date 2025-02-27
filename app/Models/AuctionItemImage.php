<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuctionItemImage extends Model
{
    use HasFactory;

    protected $fillable = ['url'];

    public function imageable()
    {
        return $this->morphTo();
    }
}
