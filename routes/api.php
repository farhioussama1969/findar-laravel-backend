<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\States\StatesController;
use App\Http\Controllers\Advertisements\AdvertisementsController;
use App\Http\Controllers\Favorites\FavoritesController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Authentication\AuthenticationController;
use App\Http\Controllers\Reviews\ReviewsController;
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
Route::middleware('auth:sanctum')->get('/advertisements/prices', [AdvertisementsController::class, 'pricesRange']);
Route::middleware('auth:sanctum')->get('/advertisements/details/{id}', [AdvertisementsController::class, 'advertisementDetails']);
Route::middleware('auth:sanctum')->get('/advertisements/my-advertisements', [AdvertisementsController::class, 'myAdvertisementsList']);
Route::middleware('auth:sanctum')->delete('/advertisements/my-advertisements', [AdvertisementsController::class, 'deleteAdvertisement']);
Route::middleware('auth:sanctum')->post('/advertisements/my-advertisements', [AdvertisementsController::class, 'addAdvertisement']);


//favorites routes
Route::middleware('auth:sanctum')->post('/favorites', [FavoritesController::class, 'addToFavorites']);
Route::middleware('auth:sanctum')->delete('/favorites', [FavoritesController::class, 'deleteFromFavorites']);
Route::middleware('auth:sanctum')->get('/favorites', [FavoritesController::class, 'favoritesList']);

//reviews
Route::middleware('auth:sanctum')->get('/reviews/{id}', [ReviewsController::class, 'reviewsList']);
Route::middleware('auth:sanctum')->post('/reviews', [ReviewsController::class, 'addReview']);
