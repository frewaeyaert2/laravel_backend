<?php

namespace App\Jobs;


use App\Models\Auction;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ActivateAuction implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    public $auctionId;

    public function __construct($auctionId)
    {
        $this->auctionId = $auctionId;
    }

    public function handle()
    {
        $auction = Auction::findOrFail($this->auctionId);
        $auction->update(['active' => true]);
    }
}
