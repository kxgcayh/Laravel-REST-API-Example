<?php

use App\Http\Controllers\API\GalleryController;
use App\Http\Controllers\API\PhotoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ProfileController;
use App\Models\Gallery;
use App\Models\User;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
    Route::post('register', [UserController::class, 'register'])->name('register');
    Route::post('login', [UserController::class, 'login'])->name('login');
    Route::post('logout', [UserController::class, 'logout']);
});


Route::group(['middleware' => 'auth.jwt'], function () {
    Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
        Route::get('/', [ProfileController::class, 'index']);
        Route::post('update', [ProfileController::class, 'update']);
    });

    Route::group(['prefix' => 'gallery', 'as' => 'gallery.'], function () {
        Route::get('/', [GalleryController::class, 'index']);
        Route::get('/show/{user_id}', [GalleryController::class, 'show']);
        Route::get('/show-galleries', [GalleryController::class, 'showGalleries']);
        Route::post('/store', [GalleryController::class, 'store']);
        Route::post('/update', [GalleryController::class, 'update']);
    });

    Route::group(['prefix' => 'photo', 'as' => 'photo.'], function () {
        Route::get('/', [PhotoController::class, 'index']);
        Route::get('/show/{user_id}', [PhotoController::class, 'show']);
        Route::post('/show-photos', [PhotoController::class, 'showPhotos']);
        Route::post('/store', [PhotoController::class, 'store']);
        Route::post('/update', [PhotoController::class, 'update']);
    });
    Route::get('users', [UserController::class, 'userList']);
});
