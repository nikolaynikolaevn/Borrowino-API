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

Route::apiResource('offer-requests', 'OfferRequestController');

Route::middleware('auth:api')->group(function () {
    Route::post('logout', 'AuthController@logout')->name('logout');

    Route::apiResource('offers', 'OfferController')->except(['index', 'show']);

    Route::middleware('admin')->group(function(){
        Route::get('/admin/users', 'adminController@showUsers')->name('admin.users');
        Route::get('/admin/users/{user}', 'adminController@showUser')->name('admin.users.show');
        Route::delete('admin/users/{user}', 'adminController@deleteUser')->name('offer.users.delete');
        Route::get('/admin/offers', 'adminController@viewOffers')->name('admin.offers');
        Route::get('/admin/offers/{offer}', 'adminController@viewOffer')->name('admin.offers.show');
        Route::delete('admin/offers/{offer}', 'adminController@deleteOffer')->name('offer.offers.delete');
    });
});


