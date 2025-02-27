<?php

namespace App\Http\Controllers;

use App\Jobs\ActivateAuction;
use App\Jobs\DeactivateJob;
use App\Models\Auction;
use App\Models\AuctionType;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Helpers\Azure\AzureBlobHelper;
use Mail;

class AuctionController extends Controller
{
    public function index(Request $request)
    {
        //Log::info(request()->all());
        $perPage = $request->query('per_page');
        $page = $request->query('page');

        try {
            $validatedData = $this->validateQueryparams($request);

            // Validate per_page and page parameters with max values
            $request->validate([
                'per_page' => 'integer|min:1|max:100',
                'page' => 'integer|min:1|max:1000',
            ]);

            $auctions = Auction::query();
            Log::info($validatedData);

            foreach ($validatedData as $key => $value) {
                $auctions->where($key, 'like', '%' . $value . '%');
            }
            // take the correct amount of items on the correct page
            return $auctions->with("images")->paginate($perPage, ['*'], 'page', $page);
        } catch (ValidationException $e) {
            Log::error('Validation error: ', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    private function validateQueryparams(Request $request)
    {
        return $request->validate([
            'id' => 'integer|exists:auctions,id',
            'auction_type_id' => 'integer|exists:auction_types,id',
            'active' => 'boolean',
            'published' => 'boolean',
            'name' => 'string',
            'start_date' => 'date',
            'end_date' => 'date',
        ]);
    }

    public function fetchFutureAuctions(Request $request)
    {
        $acutionTypeId = $request->input('type');

        if (!$acutionTypeId) {
            return response()->json([
                'status' => false,
                'message' => 'Please provide auction type id'
            ]);
        }

        $auctions = Auction::where('start_date', '>', Carbon::now())
            ->Where('auction_type_id', $acutionTypeId)
            ->get();
        return response()->json($auctions);
    }

    private function validateAuction(Request $request)
    {
        // VALIDATION
        $validation = Validator::make(
            $request->all(),
            [
                "type" => "required|integer|exists:auction_types,id",
                'name' => "required|string",
                "description" => "required|string",
                "short_description" => "required|string",
                "start_date" => "required|date|after:today",
                "end_date" => "required|date|after:start_date",
                'images.*' => 'nullable|file|mimes:jpg,png,jpeg|max:5120', // max 5MB
                "published" => "required|boolean"
            ]
        );

        $validation->after(
            function ($validator) use ($request) {
                $startDate = Carbon::parse($request->input('start_date'));
                $endDate = Carbon::parse($request->input('end_date'));
                $now = Carbon::now();

                if ($startDate->diffInMinutes($endDate) < 5) {
                    $validator->errors()->add('start_date', 'The start date must be earlier must be at least 5 mintues apart from end_date.');
                }
                if ($startDate->lessThanOrEqualTo($now)) {
                    $validator->errors()->add('start_date', 'The start date must be later than the time now!');
                }
            }
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                "message" => 'Validation Error',
                "errors" => $validation->errors()
            ], 403);
        }
    }


    public function store(Request $request)
    {
        // turn the published boolean into a number
        if ($request->has('published')) {
            $request->merge(['published' => $request->published ? 1 : 0]);
        }
        // VALIDATION
        $this->validateAuction($request);

        try {
            $auction = Auction::create(
                [
                    'auction_type_id' => $request->input('type'),
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'short_description' => $request->input('short_description'),
                    'start_date' => $request->input('start_date'),
                    'end_date' => $request->input('end_date'),
                    'published' => $request->input('published')
                ]
            );

            // Handle image upload

            $azureHelper = new AzureBlobHelper();

            if ($request->hasFile('images')) {
                //log
                Log::info('files has images');
                Log::info($request->file('images'));
                foreach ($request->file('images') as $file) {
                    Log::info('file: ' . $file);
                    $uploadedUrl = $azureHelper->uploadImageToAzure($file, 'auctions');
                    if ($uploadedUrl) {
                        Log::info('uploadedUrl: ' . $uploadedUrl);
                        $auction->images()->create(['url' => $uploadedUrl]);
                    }
                }
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }

        //SENDING BACK RESPONSE
        ActivateAuction::dispatch($auction->id)->delay(Carbon::parse($auction->start_date));

        return response()->json([
            'status' => true,
            'message' => 'Auction stored succesfully and will be activiated at ' . $request->input('start_date'),
            'data' => $auction->id
        ], 201);
    }

    public function show($id)
    {
        // Retrieve the auction object
        $auction = Auction::with('images')->findOrFail($id);


        if (!$auction) {
            return response()->json([
                'message' => 'Auction not Found!'
            ]);
        }

        $auction->images;
        if ($auction->auction_type_id == 1) {
            $items = $auction->pigeons->where("published", true)->map(function ($pigeon) {
                $item = Item::where('auction_item_id', $pigeon->id)
                    ->where('type', 'pigeon')
                    ->first();
                return [
                    'id' => $pigeon->id,
                    'auction_id' => $pigeon->auction_id,
                    'item_id' => $item ? $item->id : null,
                    'published' => $pigeon->published,
                    'title' => $pigeon->title,
                    'short_description' => $pigeon->short_description,
                    'description' => $pigeon->description,
                    'ring_number' => $pigeon->ring_number,
                    'color' => $pigeon->color,
                    'sex' => $pigeon->sex,
                    'end_date' => $pigeon->end_date,
                    'name' => $pigeon->name,
                    'image_path' => $pigeon->image_path, // Assuming you also need an image path for pigeons
                    'created_at' => $pigeon->created_at,
                    'updated_at' => $pigeon->updated_at,
                    'images' => $pigeon->images->map(function ($image) {
                        return $image->url;
                    }),
                    'highestBid' => $item ? $item->getHighestBidAttribute() : null
                ];
            });
            $auction->pigeons();
        } else if ($auction->auction_type_id == 2) {
            $items = $auction->vouchers->where("published", true)->map(function ($voucher) {
                $item = Item::where('auction_item_id', $voucher->id)
                    ->where('type', 'voucher')
                    ->first();
                return [
                    'id' => $voucher->id,
                    'auction_id' => $voucher->auction_id,
                    'item_id' => $item ? $item->id : null,
                    'published' => $voucher->published,
                    'title' => $voucher->title,
                    'enthusiast' => $voucher->enthusiast,
                    'address_enthusiast' => $voucher->address_enthusiast,
                    'end_date' => $voucher->end_date,
                    'description' => $voucher->description,
                    'short_description' => $voucher->short_description,
                    'image_path' => $voucher->image_path,
                    'created_at' => $voucher->created_at,
                    'updated_at' => $voucher->updated_at,
                    'images' => $voucher->images->map(function ($image) {
                        return $image->url;
                    }),
                    'highestBid' => $item ? $item->getHighestBidAttribute() : null
                ];
            });
        }


        return response()->json([
            'auction' => $auction,
            'items' => $items
        ]);
    }



    public function all()
    {
        // Fetch all auctions with their associated images
        $auctions = Auction::with('images')->get();

        return response()->json($auctions);
    }

    public function getAuctionType($id)
    {
        $validation = Validator::make(
            ['id' => $id],
            ['id' => 'required|integer|exists:auction_types,id']
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validation->errors(),
            ], 403);
        }

        $auctions = Auction::where('auction_type_id', $id)->get();

        if (!$auctions) {
            return response()->json([
                'status' => false,
                'message' => 'No auction found matching the provided type',
            ], 404);
        }

        // Return the AuctionType data
        return response()->json([
            'status' => true,
            'auctions' => $auctions,
        ]);
    }


    public function destroy($id)
    {
        $auction = Auction::with('images')->findOrFail($id);

        // Verwijder alle afbeeldingen van de veiling
        $azureHelper = new AzureBlobHelper();
        foreach ($auction->images as $image) {
            $azureHelper->deleteImageFromAzure($image->url);
            $image->delete();
        }

        // Verwijder de veiling
        $auction->delete();

        return response()->json(['message' => 'Auction deleted successfully.']);
    }

    public function getPublishedAuctions()
    {
        try {
            // Fetch all published auctions with their associated images
            $auctions = Auction::where('published', true)->with('images')->get();

            return response()->json($auctions, 200);
        } catch (Exception $e) {
            Log::error('Error fetching published auctions: ', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch published auctions'], 500);
        }
    }

    public function finishAuction(int $id)
    {;
        $auction = Auction::find($id);
        $type = '';
        $itemables = null;

        if (!$auction) {
            return response()->json([
                'status' => false,
                'message' => 'Auction not found'
            ], 404);
        }

        if ($auction->active) {
            $auction->update(['active' => false]);
        }

        Log::info('Auction type ID ALMOST WIN: ' . $auction->auction_type_id);

        switch ($auction->auction_type_id) {
            case 1:
                $itemables = $auction->pigeons;
                $type = 'pigeon';
                break;
            case 2:
                $itemables = $auction->vouchers;
                $type = 'voucher';
                break;
            default:
                throw new Exception("Invalid auction type ID: {$auction->auction_type_id}");
        }

        Log::info('itemables: ' . $itemables);

        foreach ($itemables as $itemable) {
            $item = Item::where('auction_item_id', $itemable->id)
                ->where('type', $type)
                ->first();

            if ($item) {
                $this->sendWinnerEmail($item);
            } else {
                Log::info('No item found for auction_item_id: ' . $itemable->id . ' and type: ' . $type);
            }
        }
    }


    private function sendWinnerEmail($item)
    {
        $highestBid = $item->bids()->orderBy('bid', 'desc')->first();
        $itemable = $item->itemable;

        if ($highestBid) {
            $winner = User::find($highestBid->user_id);

            if ($winner) {
                // Log winner details
                Log::info('Winner: ' . $winner->first_name);

                // Send the email
                Mail::send('emails.winner', [
                    'winner_first_name' => $winner->first_name,
                    'winner_last_name' => $winner->last_name,   
                    'itemable' => $itemable,
                    'highestBid' => $highestBid,
                ], function ($message) use ($winner) {
                    $message->to($winner->email)
                        ->subject('Congratulations on Winning the Auction!');
                });
            } else {
                Log::info('No user found for user ID: ' . $highestBid->user_id);
            }
        } else {
            Log::info('No bids found for item ID: ' . $item->id);
        }
    }




    public function update(Request $request, $id)
    {
        // Valideer de request, use the validateAuction method
        $this->validateAuction($request);

        // Zoek de veiling
        $auction = Auction::with('images')->findOrFail($id);

        // Update de veilinggegevens
        $auction->update($request->except('images'));

        // Controleer of er een nieuwe afbeelding is geüpload
        if ($request->hasFile('images')) {
            $azureHelper = new AzureBlobHelper();

            // Verwijder de oude afbeeldingen
            foreach ($auction->images as $image) {
                $azureHelper->deleteImageFromAzure($image->url);
                $image->delete();
            }

            // Upload de nieuwe afbeeldingen
            foreach ($request->file('images') as $file) {
                $newFileUrl = $azureHelper->uploadImageToAzure($file);
                if ($newFileUrl) {
                    $auction->images()->create(['url' => $newFileUrl]);
                }
            }
        }

        return response()->json(['message' => 'Auction updated successfully.', 'auction' => $auction]);
    }

    function publish($id)
    {
        // do begintransaction
        DB::beginTransaction();
        try {
            $auction = Auction::find($id);
            $auction->published = 1;
            $auction->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Auction published successfully'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to publish auction'
            ]);
        }
    }
}
