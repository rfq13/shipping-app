<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// A. logic Test
Route::get('placement/{numberid}', [\App\Http\Controllers\ContainerPlacementController::class,'index']);

// B. Studi kasus
Route::post('login',[AuthController::class,'login']);
Route::post('register',[AuthController::class,'register']);
Route::post('/reset-password', [AuthController::class,'password_update']);
Route::get('/reset-password', [AuthController::class,'reset_password'])->name('reset_password');
Route::post('/forgot-password', [AuthController::class,'forgot_password'])->middleware('guest')->name('password.email');

Route::group(['middleware'=>'auth:sanctum'], function () {
    Route::post('user',[AuthController::class,'user']);
    Route::post('logout',[AuthController::class,'logout']);
    
    Route::group(['prefix'=>'wallet','name'=>'wallet.'], function () {
        Route::post('/',[WalletController::class,'topup']);
        Route::get('/',[WalletController::class,'balance']);
        Route::put('/',[WalletController::class,'transfer']);
        Route::get('mutation',[WalletController::class,'mutation']);
        Route::get('wd-account',[WalletController::class,'getWithdrawalAcc']);
        Route::post('wd-account',[WalletController::class,'addWithdrawalAcc']);
        Route::post('withdraw',[WalletController::class,'withdraw']);
    });

});