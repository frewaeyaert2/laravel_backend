<?php

use App\Http\Controllers\AuctionController;
use App\Http\Controllers\AuctionTypeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AutoBidController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PigeonController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Register API routes for your application. These routes are loaded by
| the RouteServiceProvider within a group which is assigned the "api"
| middleware group.
|
*/

Route::middleware('localization')->group(function () {
    Route::get('translations', [TranslationController::class, 'all']);
});

Route::post('register', [AuthController::class,'createUser']);

Route::post('login', [AuthController::class, 'loginUser']);


//TO DELETE

Route::get('users', [UserController::class, 'index']);
Route::get('items', [ItemController::class, 'index']);

// Auctions
Route::get('auctions/{id}', [AuctionController::class, 'show']);
Route::get('published', [AuctionController::class, 'getPublishedAuctions']); // Resource route for auctions


// Pigeons
Route::get('pigeons', [PigeonController::class, 'index']);
Route::get('pigeons/{id}', [PigeonController::class, 'show']);

// Vouchers
Route::get('vouchers', [VoucherController::class, 'all']);
Route::get('vouchers/{id}', [VoucherController::class, 'show']);

// Users
Route::post('/forgot-password', [UserController::class, 'sendForgotPasswordEmail']);

Route::post('/reset-password', [UserController::class, 'updatePassword']);



Route::get('jobs', function () {
    return DB::table('jobs')->get();
});

Route::get('tokens', function () {
    return DB::table('personal_access_tokens')->get();
});

Route::middleware(['auth:sanctum', 'admin'])
    ->prefix('admin')
    ->group(function() {
        // Admin-specific routes
        Route::get('users', [UserController::class, 'index']); // Users
        Route::get('users/{id}', [UserController::class, 'show']); // User
        Route::delete('users/{id}', [UserController::class, 'destroy']); // Delete User

        // Auctions
        Route::get("auctions", [AuctionController::class, 'index']); // Fetch all auctions
        Route::get("auctions/future", [AuctionController::class, 'fetchFutureAuctions']); // Fetch future auctions
        Route::get("auctions/{id}", [AuctionController::class, 'show']); // Fetch single auction
        Route::get('auctions/type/{id}', [AuctionController::class, 'getAuctionType']);
        Route::post('auctions', [AuctionController::class, 'store']); // Create Auction
        Route::put('auctions/{id}/publish', [AuctionController::class, 'publish']); // Publish Auction
        Route::put('auctions/{id}', [AuctionController::class, 'update']); // Update Auction

        Route::delete('auctions/{id}', [AuctionController::class, 'destroy']); // Delete Auction

        // Pigeons
        Route::post('pigeons', [PigeonController::class, 'store']);
        Route::delete('pigeons/{id}', [PigeonController::class, 'destroy']);
        Route::put('pigeons/{id}/publish', [PigeonController::class, 'publish']);
        Route::put('pigeons/{id}', [PigeonController::class, 'update']);

        // Vouchers
        Route::post('vouchers', [VoucherController::class, 'store']);
        Route::put('vouchers/{id}/publish', [VoucherController::class, 'publish']); // Publish Voucher
        Route::put('vouchers/{id}', [VoucherController::class, 'update']);
        Route::delete('vouchers/{id}', [VoucherController::class, 'destroy']);

        // Bids
        Route::get('bids/{id}', [BidController::class, 'find']);

        // Items
        Route::delete('items/{id}', [ItemController::class, 'destroy']);

        Route::patch('auctions/{id}/finish', [AuctionController::class, 'finishAuction']);
    });

Route::middleware(['auth:sanctum'])
    ->group(function(){
        //Auction Types
        Route::get('auction-types', [AuctionTypeController::class, 'index']);

        Route::resource('auction-types', AuctionTypeController::class);
        Route::resource('items', ItemController::class);

        //Bids
        Route::resource('bids', BidController::class);

        //Auto Bids
        Route::get('autoBids', [AutoBidController::class, 'all']);
        Route::post('autoBids', [AutoBidController::class, 'create']);
        Route::get('autoBids/highest/{itemId}', [AutobidController::class, 'getHighestAutobid']);

        //User Profile
        Route::get('my-profile', [UserController::class, 'show']);
        Route::patch('my-profile', [AuthController::class, 'update']);
        Route::post('logout', [AuthController::class, 'logout']);

});


// Check Authentication Status
Route::get('check-admin', [AuthController::class, 'checkAdmin']);
Route::get('auth/me', [AuthController::class, 'me']);
Route::get('check-auth', [AuthController::class, 'checkAuth']);

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationNotification'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');


