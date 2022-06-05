<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\States\StatesController;
use App\Http\Controllers\Advertisements\AdvertisementsController;
use App\Http\Controllers\Favorites\FavoritesController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Authentication\AuthenticationController;
use App\Http\Controllers\Reviews\ReviewsController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Notifications\NotificationsController;
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
Route::post('/states', [StatesController::class, 'addStates']);

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

//user
Route::middleware('auth:sanctum')->get('/user/statistic', [UserController::class, 'statistic']);
Route::middleware('auth:sanctum')->put('/user/informations', [UserController::class, 'updateInformations']);
Route::middleware('auth:sanctum')->put('/user/changepassword', [UserController::class, 'changePassword']);

//home
Route::middleware('auth:sanctum')->get('/home', [HomeController::class, 'index']);
Route::middleware('auth:sanctum')->get('/settings/privacypolicy', [SettingsController::class, 'privacyPolicy']);

//notifications
Route::middleware('auth:sanctum')->get('/notifications', [NotificationsController::class, 'notificationsList']);
Route::middleware('auth:sanctum')->put('/notifications', [NotificationsController::class, 'markAsRead']);
Route::middleware('auth:sanctum')->get('/notifications/count', [NotificationsController::class, 'notificationsCount']);


