<?php

namespace App\Jobs;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;


// U CAN USE THIS CLASS IN THE FUTURE TO IMPLEMENT AUTOMATIC DEACTIVATION OF AUCTIONS
class DeactivateJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $auctionId;

    public function __construct($auctionId)
    {
        $this->auctionId = $auctionId;
    }

    public function uniqueId(){
        return $this->auctionId; 
    }

    
    public function handle(): void
    {
        $auction = Auction::findOrFail($this->auctionId);
        $auction->update(['active' => false]);
    }
}
