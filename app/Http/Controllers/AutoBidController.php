<?php

namespace App\Http\Controllers;

use App\Http\Services\AutoBidService;
use App\Models\AutoBid;
use Illuminate\Http\Request;

class AutoBidController extends Controller
{

    protected AutoBidService $autoBidService;
    
    public function __construct(AutoBidService $autoBidService){
        $this->autoBidService = $autoBidService;
    }

    public function all(){
        return $this->autoBidService->getAll();
    }

    public function create(Request $request){
        $userId =  auth('sanctum')->user()->id;
        $createdAutoBid = $this->autoBidService->create($request, $userId);
        return $createdAutoBid;
       
    }

    public function autoBid(){

    }

    public function getHighestAutobid($itemId){
        $user =  auth('sanctum')->user();
        
        // Check if the user is authenticated
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }

        // Fetch the highest autobid for the given item by the authenticated user
        $highestAutobid = Autobid::where('item_id', $itemId)
            ->where('user_id', $user->id)
            ->orderBy('max_bid', 'desc')
            ->first();

        // Check if an autobid was found
        if (!$highestAutobid) {
            return response()->json([
                'message' => 'No autobid found for this item'
            ], 404);
        }

        // Return the highest autobid
        return response()->json([
            'highest_autobid' => $highestAutobid
        ]);
    }

}
