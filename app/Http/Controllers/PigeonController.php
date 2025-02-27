<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\AuctionType;
use App\Models\AuctionItem;
use App\Models\Item;
use App\Models\Pigeon;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\Azure\AzureBlobHelper;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PigeonController extends Controller
{
    public function index()
    {
        $pigeons = Pigeon::all();
        return $pigeons;
    }


    public function store(Request $request)
    {
        // turn the boolean published into a number

        try {
            $validatedData = $this->validateRequest($request);

            // Retrieve the auction by ID and ensure it exists
            $auction = $this->getAuction($validatedData['auction_id']);
            $this->isAuctionTypePigeon($auction);

            // Automatically assign the auction's end_date to the new pigeon
            $validatedData['end_date'] = $auction->end_date;

            // Create the pigeon with the inherited end_date
            $pigeon = Pigeon::create($validatedData);

            // Handle image uploads if provided
            $azureHelper = new AzureBlobHelper();
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $uploadedUrl = $azureHelper->uploadImageToAzure($file, 'pigeons');
                    if ($uploadedUrl) {
                        $pigeon->images()->create(['url' => $uploadedUrl]);
                    }
                }
            }

            return response()->json([
                'message' => 'Pigeon created successfully!',
                'pigeon' => $pigeon
            ], 201);
        } catch (ValidationException $e) {
            Log::error('Validation error: ', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error creating pigeon: ', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create pigeon'], 500);
        }
    }

    private function validateRequest(Request $request)
    {
        // check if published is true or false
        if ($request->has('published')) {
            $request->merge(['published' => $request->published ? 1 : 0]);
        }

        return $request->validate([
            'auction_id' => 'required|integer|exists:auctions,id',
            'title' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'short_description' => 'required|string|max:255',
            'description' => 'required|string',
            'ring_number' => 'required|string|max:255',
            'sex' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'images.*' => 'nullable|file|mimes:jpg,png,jpeg|max:5120', // max 5MB
            'published' => 'required|boolean'
        ]);
    }

    private function getAuction($auctionId)
    {
        return Auction::findOrFail($auctionId);
    }

    private function isAuctionTypePigeon($auction)
    {
        $pigeonTypeId = AuctionType::where('type', 'pigeon')->value('id');

        if ($auction->auction_type_id != $pigeonTypeId) {
            throw ValidationException::withMessages(['auction_id' => 'The auction is not of type pigeon']);
        }

        return true;
    }

    public function show($id)
    {
        $pigeon = Pigeon::with('auction')->findOrFail($id);

        if (!$pigeon) {
            return response()->json([
                'status' => false,
                'message' => 'pigeon not found'
            ], 404);
        }

        $item = Item::where('auction_item_id', $pigeon->id)
            ->where('type', 'pigeon')
            ->first();

        $highestBidAmount = 0;
        $itemId = $item ? $item->id : null;
        $highestBidderName = null;

        if ($item) {
            $highestBid = $item->bids()->orderBy('bid', 'desc')->first();
            if ($highestBid) {
                $highestBidAmount = $highestBid->bid;
                $highestBidder = $highestBid->user;
                if ($highestBidder) {
                    $highestBidderName = $highestBidder->first_name . ' ' . $highestBidder->last_name;
                }
            }
        }

        $pigeon->images;


        //log the item
        Log::info('Item: ' . $item);

        return response()->json([
            'pigeon' => $pigeon,
            'item_id' => $itemId,
            'highest_bid' => $highestBidAmount,
            'highest_bidder' => $highestBidderName,
        ], 200);
    }




    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // retrieve the payload from the request
        Log::info('request: ');
        Log::info($request);
        Log::info($request->all());
        // Valideer de request, use the validateAuction method
        $this->validatePigeon($request);

        // Zoek de veiling
        $pigeonId = $request->route('id');
        Log::info('pigeonId: ');
        $pigeon = Pigeon::with('images')->findOrFail($pigeonId);

        // Update de veilinggegevens
        $pigeon->update($request->except('images'));

        // Controleer of er een nieuwe afbeelding is geüpload
        if ($request->hasFile('images')) {
            $azureHelper = new AzureBlobHelper();

            // Verwijder de oude afbeeldingen
            foreach ($pigeon->images as $image) {
                $azureHelper->deleteImageFromAzure($image->url);
                $image->delete();
            }

            // Upload de nieuwe afbeeldingen
            foreach ($request->file('images') as $file) {
                $newFileUrl = $azureHelper->uploadImageToAzure($file);
                if ($newFileUrl) {
                    $pigeon->images()->create(['url' => $newFileUrl]);
                }
            }
        }

        return response()->json(['message' => 'Pigeon updated successfully.', 'auction' => $pigeon], 200);
    }

    private function validatePigeon(Request $request)
    {
        if ($request->has('published')) {
            $request->merge(['published' => $request->published ? 1 : 0]);
        }

        $validation = Validator::make(
            $request->all(),
            [
            'auction_id' => 'required|integer|exists:auctions,id',
            'title' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'short_description' => 'required|string|max:255',
            'description' => 'required|string',
            'ring_number' => 'required|string|max:255',
            'sex' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'images.*' => 'nullable|file|mimes:jpg,png,jpeg|max:5120', // max 5MB
            'published' => 'required|boolean'
            ]
        );

        // validate if the auction is of type pigeon
        $auction = Auction::findOrFail($request->auction_id);
        if ($auction->auction_type_id != AuctionType::where('type', 'pigeon')->first()->id) {
            $validation->errors()->add('auction_id', 'The auction is not of type pigeon');
        }


        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                "message" => 'Validation Error',
                "errors" => $validation->errors()
            ], 403);
        };
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pigeon = Pigeon::with('images')->findOrFail($id);

        // Remove images from pigeon
        $azureHelper = new AzureBlobHelper();
        foreach ($pigeon->images as $image) {
            $azureHelper->deleteImageFromAzure($image->url);
            $image->delete();
        }

        // Verwijder de duif
        $pigeon->delete();

        return response()->json([
            'message' => 'Pigeon deleted successfully'
        ], 204);
    }

    public function publish($id)
    {
        DB::beginTransaction();
        try {
            $pigeon = Pigeon::find($id);
            $pigeon->published = 1;
            $pigeon->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Pigeon published successfully'
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to publish pigeon'
            ], 500);
        }
    }
}
