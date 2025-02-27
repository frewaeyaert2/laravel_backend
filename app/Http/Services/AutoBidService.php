<?php

namespace App\Http\Services;

use App\Models\Auction;
use App\Models\AutoBid;
use App\Models\Bid;
use App\Models\Item;
use App\Models\Pigeon;
use App\Models\Voucher;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Log;
use Validator;

class AutoBidService
{
    public function getAll()
    {
        return AutoBid::orderBy('item_id')
            ->orderBy('bid', 'desc')
            ->get();
    }

    public function create(Request $request, $userId)
    {
        $validAutoBid = $this->validateAutoBid($request, $userId);

        if ($validAutoBid !== true) {
            return $validAutoBid;
        }

        return $this->storeAutoBid($request, $userId);
    }

    private function validateAutoBid(Request $request, $userId)
    {
        $validation = Validator::make($request->all(), [
            'max_bid' => 'required|integer',
            'item_id' => 'required|exists:items,id'
        ]);
    
        $validation->after(function ($validator) use ($request, $userId) {
            $highestBid = $this->getHighestBid($request->item_id);
            $highestAutoBid = $this->getTopTwoAutoBids($request->item_id)->first();
    
            $this->validateBidIncrement($highestBid, $request->input('max_bid'), $validator);
    
            if ($highestAutoBid) {
                $requiredIncrement = $this->getMinimumIncrement($highestAutoBid->max_bid);
                if ($request->input('max_bid') < ($highestAutoBid->max_bid + $requiredIncrement)) {
                    $validator->errors()->add(
                        'max_bid',
                        "Your AutoBid must be at least {$requiredIncrement} higher than the current highest AutoBid of {$highestAutoBid->max_bid}."
                    );
                }
            }
        });
    
        if ($validation->fails()) {
            return response()->json([
                "message" => 'Validation Error',
                "error" => $validation->errors()
            ], 403);
        }
    
        return true;
    }
    
    private function storeAutoBid(Request $request, $userId)
    {
        $autoBidData = [
            'max_bid' => $request->input("max_bid"),
            'date_time' => Carbon::now()->toISOString(),
            'user_id' => $userId,
            'item_id' => $request->input('item_id'),
        ];

        $result = $this->processAutoBid((object)$autoBidData);

        if ($result['success']) {
           
            $storedAutoBid = AutoBid::create($autoBidData);

            
            $result['auto_bid'] = $storedAutoBid;

            return response()->json([
                'message' => 'AutoBid stored successfully',
                'success' => true,
                'auto_bid' => $storedAutoBid,
                'current_highest_bid' => $result['current_highest_bid'],
                'current_highest_auto_bid' => $result['current_highest_auto_bid'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to process AutoBid',
        ], 500);
    }

    private function processAutoBid($tempAutoBid)
{
    $highestBid = $this->getHighestBid($tempAutoBid->item_id);
    $highestAutoBid = $this->getTopTwoAutoBids($tempAutoBid->item_id)->first();

    if (!$highestAutoBid) {
        // If no existing AutoBids, create a new bid directly
        $newBidAmount = $this->calculateNextValidBid($highestBid, $tempAutoBid->max_bid);
        $this->createBid($newBidAmount, $tempAutoBid->user_id, $tempAutoBid->item_id);

        return [
            'success' => true,
            'current_highest_bid' => [
                'amount' => $newBidAmount,
                'user_id' => $tempAutoBid->user_id,
            ],
            'current_highest_auto_bid' => [
                'amount' => $tempAutoBid->max_bid,
                'user_id' => $tempAutoBid->user_id,
            ],
        ];
    }

    // Compare new AutoBid with the highest existing AutoBid
    if ($tempAutoBid->max_bid > $highestAutoBid->max_bid) {
        // If the new AutoBid exceeds the highest AutoBid
        $newBidAmount = $highestAutoBid->max_bid + $this->getMinimumIncrement($highestAutoBid->max_bid);
        $this->createBid($newBidAmount, $tempAutoBid->user_id, $tempAutoBid->item_id);

        return [
            'success' => true,
            'current_highest_bid' => [
                'amount' => $newBidAmount,
                'user_id' => $tempAutoBid->user_id,
            ],
            'current_highest_auto_bid' => [
                'amount' => $tempAutoBid->max_bid,
                'user_id' => $tempAutoBid->user_id,
            ],
        ];
    }

    // If the new AutoBid does not exceed the highest AutoBid
    $newBidAmount = max(
        $tempAutoBid->max_bid,
        $highestBid + $this->getMinimumIncrement($highestBid)
    );
    $this->createBid($newBidAmount, $highestAutoBid->user_id, $tempAutoBid->item_id);

    return [
        'success' => true,
        'current_highest_bid' => [
            'amount' => $newBidAmount,
            'user_id' => $highestAutoBid->user_id,
        ],
        'current_highest_auto_bid' => [
            'amount' => $highestAutoBid->max_bid,
            'user_id' => $highestAutoBid->user_id,
        ],
    ];
}

    

    private function createBid($amount, $userId, $itemId)
    {
        $bid = Bid::create([
            'bid' => $amount,
            'date_time' => Carbon::now()->toISOString(),
            'user_id' => $userId,
            'item_id' => $itemId,
        ]);
    
        $this->extendEndDate($itemId);
    
        return $bid;
    }

    private function getHighestBidUser($itemId)
    {
        return Bid::where('item_id', $itemId)
            ->orderBy('bid', 'desc')
            ->first()?->user_id;
    }

    private function validateBidIncrement($highestBid, $maxBid, $validator)
    {
        $currentIncrement = $this->getMinimumIncrement($highestBid);

        $threshold = ceil($highestBid / 10) * 10;
        $nextIncrement = $this->getMinimumIncrement($threshold);

        if ($maxBid > $threshold) {
            if ($maxBid < ($highestBid + $nextIncrement)) {
                $validator->errors()->add('max_bid', "Your AutoBid must be at least {$nextIncrement} higher than the current highest bid of {$highestBid}.");
            }
        } else {
            if ($maxBid <= ($highestBid + $currentIncrement)) {
                $validator->errors()->add('max_bid', "Your AutoBid must be at least {$currentIncrement} higher than the current highest bid of {$highestBid}.");
            }
        }
    }

    private function getHighestBid($itemId)
    {
        return Bid::where('item_id', $itemId)->max('bid') ?? 0;
    }

    private function getLastAutoBid($itemId)
    {
        return AutoBid::where('item_id', $itemId)
            ->orderBy('date_time', 'desc')
            ->first();
    }

    private function getTopTwoAutoBids($itemId)
    {
        return AutoBid::where('item_id', $itemId)
            ->orderBy('max_bid', 'desc')
            ->take(2)
            ->get();
    }

    private function getMinimumIncrement($currentBid)
    {
        if ($currentBid < 250) {
            return 10;
        } elseif ($currentBid < 1000) {
            return 25;
        } else {
            return 100;
        }
    }

    private function calculateNextValidBid($currentBid, $maxBid, $secondHighestAutoBid = null)
    {
        $nextBid = $currentBid + $this->getMinimumIncrement($currentBid);

        if ($secondHighestAutoBid && $nextBid > $secondHighestAutoBid->max_bid) {
            $nextBid = $secondHighestAutoBid->max_bid + $this->getMinimumIncrement($secondHighestAutoBid->max_bid);
        }

        return min($nextBid, $maxBid);
    }

  private function extendEndDate($itemId)
{
    $now = Carbon::now('UTC');
    $item = Item::find($itemId);

    if (!$item) {
        return; 
    }

    $relatedItem = $item->itemable;
    if (!$relatedItem) {
        return; 
    }

    $auction = Auction::find($item->auction_id);
    if (!$auction) {
        return; 
    }

    // Compare the end date with the current time
    $itemEndDate = Carbon::parse($relatedItem->end_date, 'UTC');
    $auctionEndDate = Carbon::parse($auction->end_date, 'UTC');

    // Only extend if the current end date is less than 5 minutes from now
    if ($itemEndDate->diffInMinutes($now, false) <= 5 && $itemEndDate->lessThan($now->addMinutes(5))) {
        $newEndDate = $now->addMinutes(5);

        if ($relatedItem instanceof Pigeon) {
            Pigeon::where('id', $relatedItem->id)->update(['end_date' => $newEndDate->toISOString()]);
        } elseif ($relatedItem instanceof Voucher) {
            Voucher::where('id', $relatedItem->id)->update(['end_date' => $newEndDate->toISOString()]);
        }

        if ($auctionEndDate->lessThan($newEndDate)) {
            Auction::where('id', $auction->id)->update(['end_date' => $newEndDate->toISOString()]);
        }
    } else {
        // Log or debug if no update was required
        Log::info('End date not updated as it is more than 5 minutes from now.');
    }
}

    
}
