<?php

use App\Http\Controllers\AuctionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/email-verified-success', function (Request $request) {
    $name = $request->query('name', 'User');
    return view('verify-success', ['name' => $name]);
})->name('email.verified.success');

Route::get('/reset-password', [UserController::class, 'sendForgotPasswordEmail']);
Route::get('/forgot-password', function () {
    return view('forgot-password'); 
});
