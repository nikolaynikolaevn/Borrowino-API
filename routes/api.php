<?php

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
Route::post('/register', 'AuthController@register')->name('register');
Route::post('/login', 'AuthController@login')->name('login');

Route::get('offers', 'OfferController@index')->name('offers.index');
Route::get('offers/{offer}', 'OfferController@show')->name('offers.show');

Route::get('/offers/{offer}/images', 'OfferController@images')->name('offers.images');
Route::get('/users/{user}/images', 'WorkaroundUserController@images')->name('users.images');

Route::get('/search', 'SearchController@searchOffer')->name('search');

Route::apiResource('offer-requests', 'OfferRequestController');

Route::apiResource('users', 'UserController');

Route::middleware('auth:api')->group(function () {
    Route::post('logout', 'AuthController@logout')->name('logout');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('offers', 'OfferController')->except(['index', 'show']);

    Route::middleware('admin')->group(function(){
        Route::get('/admin/users', 'AdminController@showUsers')->name('admin.users');
        Route::get('/admin/users/{user}', 'AdminController@showUser')->name('admin.users.show');
        Route::delete('admin/users/{user}', 'AdminController@deleteUser')->name('offer.users.delete');
        Route::get('/admin/offers', 'AdminController@viewOffers')->name('admin.offers');
        Route::get('/admin/offers/{offer}', 'AdminController@viewOffer')->name('admin.offers.show');
        Route::delete('admin/offers/{offer}', 'AdminController@deleteOffer')->name('offer.offers.delete');
    });
});


