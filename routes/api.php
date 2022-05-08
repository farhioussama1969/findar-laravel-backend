<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\States\StatesController;
use App\Http\Controllers\Advertisements\AdvertisementsController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Authentication\AuthenticationController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//authentication routes
Route::post('/phonecheck', [AuthenticationController::class, 'phoneCheck']);
Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/register', [AuthenticationController::class, 'register']);
Route::middleware('auth:sanctum')->post('/logout', [AuthenticationController::class, 'logout']);

//states routes
Route::middleware('auth:sanctum')->get('/states', [StatesController::class, 'statesList']);

//advertisements routes
Route::middleware('auth:sanctum')->get('/advertisements', [AdvertisementsController::class, 'advertisementsList']);
