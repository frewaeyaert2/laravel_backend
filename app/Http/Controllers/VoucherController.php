<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\AuctionType;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Item;
use App\Helpers\Azure\AzureBlobHelper;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vouchers = Voucher::all();
        return $vouchers;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function all()
    {
        $vouchers = Voucher::all();
        return $vouchers;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // turn the boolean published into a number


        try {
            Log::info('Store voucher: ', $request->all());

            // Validate the request, excluding the end_date
            $validatedData = $this->validateRequest($request);

            // Fetch the auction and validate its type
            $auction = $this->getAuction($validatedData['auction_id']);
            $this->isAuctionTypeVoucher($auction);

            // Automatically assign the auction's end_date to the voucher
            $validatedData['end_date'] = $auction->end_date;

            // Create the voucher with the inherited end_date
            $voucher = Voucher::create($validatedData);

            // Handle image uploads if provided
            $azureHelper = new AzureBlobHelper();
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $uploadedUrl = $azureHelper->uploadImageToAzure($file, 'vouchers');
                    if ($uploadedUrl) {
                        $voucher->images()->create(['url' => $uploadedUrl]);
                    }
                }
            }

            return response()->json([
                'message' => 'Voucher created successfully',
                'voucher' => $voucher
            ], 201);
        } catch (ValidationException $e) {
            Log::error('Validation error: ', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating voucher: ', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create voucher'], 500);
        }
    }


    private function validateRequest(Request $request)
    {
        // if published is boolean turn into number
        if ($request->has('published')) {
            $request->merge(['published' => $request->published ? 1 : 0]);
        }

        return $request->validate([
            'auction_id' => 'required|integer|exists:auctions,id',
            'title' => 'required|string|max:255',
            'enthusiast' => 'required|string|max:255',
            'address_enthusiast' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'short_description' => 'required|string|max:255',
            'images.*' => 'nullable|file|mimes:jpg,png,jpeg|max:5120', // max 5MB
            'published' => 'required|boolean'
        ]);
    }

    private function getAuction($auctionId)
    {
        return Auction::findOrFail($auctionId);
    }

    private function isAuctionTypeVoucher($auction)
    {
        $voucherTypeid = AuctionType::where('type', 'voucher')->first()->id;
        Log::info('Voucher type id: ' . $voucherTypeid);

        if ($auction->auction_type_id != $voucherTypeid) {
            throw ValidationException::withMessages(['auction_id' => 'The auction is not of type voucher']);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validateVoucher($request);

        // Zoek de veiling
        $voucher = Voucher::with('images')->findOrFail($id);

        // Update de veilinggegevens
        $voucher->update($request->except('images'));

        // Controleer of er een nieuwe afbeelding is geüpload
        if ($request->hasFile('images')) {
            $azureHelper = new AzureBlobHelper();

            // Verwijder de oude afbeeldingen
            foreach ($voucher->images as $image) {
                $azureHelper->deleteImageFromAzure($image->url);
                $image->delete();
            }

            // Upload de nieuwe afbeeldingen
            foreach ($request->file('images') as $file) {
                $newFileUrl = $azureHelper->uploadImageToAzure($file);
                if ($newFileUrl) {
                    $voucher->images()->create(['url' => $newFileUrl]);
                }
            }
        }

        return response()->json(['message' => 'Voucher updated successfully.', 'auction' => $voucher], 200);
    }

    private function validateVoucher(Request $request)
    {
        if ($request->has('published')) {
            $request->merge(['published' => $request->published ? 1 : 0]);
        }

        $validation = Validator::make(
            $request->all(),
            [
                'auction_id' => 'required|integer|exists:auctions,id',
                'title' => 'required|string|max:255',
                'enthusiast' => 'required|string|max:255',
                'address_enthusiast' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'short_description' => 'required|string|max:255',
                'images.*' => 'nullable|file|mimes:jpg,png,jpeg|max:5120', // max 5MB
                'published' => 'required|boolean'
            ]
        );

        $auction = Auction::findOrFail($request->auction_id);
        if ($auction->auction_type_id != AuctionType::where('type', 'voucher')->first()->id) {
            $validation->errors()->add('auction_id', 'The auction is not of type voucher');
        }

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                "message" => 'Validation Error',
                "errors" => $validation->errors()
            ], 403);
        };
    }

    public function show($id)
    {
        $voucher = Voucher::with('auction')->findOrFail($id);

        if (!$voucher) {
            return response()->json([
                'status' => false,
                'message' => 'Voucher not found'
            ], 404);
        }

        $item = Item::where('auction_item_id', $voucher->id)
            ->where('type', 'voucher')
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

        $voucher->images;

        return response()->json([
            'voucher' => $voucher,
            'item_id' => $itemId,
            'highest_bid' => $highestBidAmount,
            'highest_bidder' => $highestBidderName,
        ], 200);
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function destroy($id)
    {
        $voucher = Voucher::with('images')->findOrFail($id);

        // Verwijder alle afbeeldingen van de voucher
        $azureHelper = new AzureBlobHelper();
        foreach ($voucher->images as $image) {
            $azureHelper->deleteImageFromAzure($image->url);
            $image->delete();
        }

        // Verwijder de voucher
        $voucher->delete();

        return response()->json(['message' => 'Voucher deleted successfully.'], 204);
    }

    public function publish($id)
    {
        DB::beginTransaction();
        try {
            $voucher = Voucher::find($id);
            $voucher->published = 1;
            $voucher->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Voucher published successfully'
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to publish voucher'
            ], 500);
        }
    }
}
