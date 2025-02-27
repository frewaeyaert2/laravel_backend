<?php

namespace App\Http\Controllers;

use App\Jobs\DeactivateJob;
use App\Models\Auction;
use App\Models\AutoBid;
use App\Models\Bid;
use App\Models\Item;
use App\Models\Pigeon;
use App\Models\User;
use App\Models\Voucher;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Validator;
use Illuminate\Support\Facades\Log;

class BidController extends Controller
{
    public function find($id)
    {
        // give the user information about the bid
        $bids = Bid::where('item_id', $id)->with('user')->get();
        Log::info('Bids: ' . $bids);
        return $bids;
    }

    public function index()
    {
        $bids = Bid::orderBy("item_id")->orderBy("date_time", "desc")->get();
        return $bids;
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'bid' => 'required|integer',
            'item_id' => 'required|integer|exists:items,id'
        ]);

        $userId = auth('sanctum')->user()->id;

        $validation->after(function ($validator) use ($request) {
            $highestBid = Bid::where('item_id', $request->item_id)->max('bid');
            $now = Carbon::now('UTC');
            $currentIncrement = $this->getMinimumIncrement($highestBid);
            $threshold = ceil($highestBid / 10) * 10;
            $nextIncrement = $this->getMinimumIncrement($threshold);

            $auction = Auction::find(Item::find($request->item_id)->auction_id);

            if ($now->greaterThan($auction->end_date)) {
                $validator->errors()->add(
                    'bid',
                    "Auction has ended. You cannot place a bid."
                );
            }

            if ($highestBid < 1000 && $request->input('bid') >= 1000) {
                if ($request->input('bid') < ($highestBid + 100)) {
                    $validator->errors()->add(
                        'bid',
                        "Your bid must be at least " . ($highestBid + 100) . ", as the next range starts at 1000 with a minimum increment of 100."
                    );
                }
            } elseif ($request->input('bid') > $threshold) {
                if ($request->input('bid') < ($highestBid + $nextIncrement)) {
                    $validator->errors()->add(
                        'bid',
                        "Your bid must be at least " . ($highestBid + $nextIncrement) . " higher than the current highest bid of " . $highestBid . "."
                    );
                }
            } else {
                if ($request->input('bid') < ($highestBid + $currentIncrement)) {
                    $validator->errors()->add(
                        'bid',
                        "Your bid must be at least " . ($highestBid + $currentIncrement) . " higher than the current highest bid of " . $highestBid . "."
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

        try {
            $bid = Bid::create([
                'bid' => $request->input("bid"),
                'date_time' => Carbon::now('UTC')->toISOString(), 
                'user_id' => $userId,
                'item_id' => $request->input('item_id'),
            ]);
        
            $item = Item::find($request->item_id);
        
            Log::info('Item: ' . $item);
        
            if (!$item) {
                return response()->json(['status' => false, 'message' => 'Item not found'], 404);
            }
        
            $relatedItem = $item->itemable;
            if (!$relatedItem) {
                return response()->json(['status' => false, 'message' => 'Related item not found'], 404);
            }
            Log::info('relItem: ' . $relatedItem);
        
            $auction = Auction::find($item->auction_id);
        
            if (!$auction) {
                return response()->json(['status' => false, 'message' => 'Auction not found'], 404);
            }
        
            // Update end date for related item
            // Update end date for related item
            $now = Carbon::now('UTC');
            $itemEndDate = Carbon::parse($relatedItem->end_date);
            $auctionEndDate = Carbon::parse($auction->end_date);

            // Check if the remaining time is less than 5 minutes
            if ($itemEndDate->diffInMinutes($now, false) <= 5) {
                // Only update if the current end date is less than 5 minutes from now
                $newEndDate = $now->addMinutes(5);

                // Ensure the new end date does not regress
                if ($itemEndDate->lt($newEndDate)) {
                    Log::info('Extending end date to 5 minutes from now: ' . $newEndDate->toISOString());

                    // Update the related item's end_date in the database
                    if ($relatedItem instanceof Pigeon) {
                        Pigeon::where('id', $relatedItem->id)->update(['end_date' => $newEndDate->toISOString()]);
                    } elseif ($relatedItem instanceof Voucher) {
                        Voucher::where('id', $relatedItem->id)->update(['end_date' => $newEndDate->toISOString()]);
                    }

                    // Update the auction's end_date in the database
                    if ($auctionEndDate->lt($newEndDate)) {
                        Auction::where('id', $auction->id)->update(['end_date' => $newEndDate->toISOString()]);
                    }
                }
            } else {
                Log::info('End date is more than 5 minutes from now. No update needed.');
            }

        
            Log::info('Updated Auction: ' . $auction);
            Log::info('Updated Item: ' . $relatedItem);
        
            return response()->json([
                'status' => true,
                'message' => 'Bid stored successfully',
                'data' => $bid->id,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
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
}
