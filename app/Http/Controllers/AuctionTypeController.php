<?php

namespace App\Http\Controllers;

use App\Models\AuctionType;
use Illuminate\Http\Request;

class AuctionTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $auctionTypes = AuctionType::all();
        return $auctionTypes;
    }
}
