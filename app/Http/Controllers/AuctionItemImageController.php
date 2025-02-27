<?php

namespace App\Http\Controllers;
use App\Helpers\Azure\AzureBlobHelper;
use App\Models\AuctionItemImage;
use Illuminate\Http\Request;

class AuctionItemImageController extends Controller
{

    protected $azureBlobHelper;

    public function __construct(AzureBlobHelper $azureBlobHelper)
    {
        $this->azureBlobHelper = $azureBlobHelper;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AuctionItemImage $auctionItemImage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AuctionItemImage $auctionItemImage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AuctionItemImage $auctionItemImage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AuctionItemImage $auctionItemImage)
    {
        //
    }
    public function uploadImage(Request $request)
    {
        // Validate the file
        $request->validate([
            'image' => 'required|file|mimes:jpg,png,jpeg|max:5120', // max 5MB
        ]);

        // Get the uploaded file
        $file = $request->file('image');

        // Upload the image to Azure and get the URL
        $uploadedUrl = $this->azureBlobHelper->uploadImageToAzure($file, 'images'); // 'images' is the folder name

        if ($uploadedUrl) {
            return response()->json(['url' => $uploadedUrl], 200);
        }

        return response()->json(['error' => 'Failed to upload image'], 500);
    }
}
