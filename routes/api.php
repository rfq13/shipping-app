<?php

use App\Http\Controllers\Api\AuthController;
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
Route::get('cekid',[AuthController::class,'cekid']);

Route::get('placement/{numberid}', [\App\Http\Controllers\ContainerPlacementController::class,'index']);

Route::post('login',[AuthController::class,'login']);
Route::post('register',[AuthController::class,'register']);
Route::post('/reset-password', [AuthController::class,'password_update']);
Route::get('/reset-password', [AuthController::class,'reset_password'])->name('reset_password');
Route::post('/forgot-password', [AuthController::class,'forgot_password'])->middleware('guest')->name('password.email');

Route::group(['middleware'=>'auth:sanctum'], function () {
    Route::post('user',[AuthController::class,'user']);
    Route::post('logout',[AuthController::class,'logout']);
});

// A. logic Test


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });