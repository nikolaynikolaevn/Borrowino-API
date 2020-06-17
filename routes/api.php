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

Route::apiResource('users', 'UserController');
Route::get('/users/{user}/offers', 'UserController@getUserOffers')->name('users.offers');
Route::get('/users/{user}/images', 'WorkaroundUserController@images')->name('users.images');

Route::get('/search', 'SearchController@searchOffer')->name('search');

Route::middleware('auth:api')->group(function () {
    Route::post('logout', 'AuthController@logout')->name('logout');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/user/offers', function (Request $request) {
        return $request->user()->offers();
    });
    Route::get('/user/received-requests', 'UserController@getReceivedOfferRequests')->name('users.received-requests');

    Route::apiResource('offers', 'OfferController')->except(['index', 'show']);
    Route::apiResource('offers.requests', 'OfferRequestController');

    Route::post('/offers/{offer}/report', 'OfferReportController@store')->name('offer-reports.store');

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('offer-reports', 'OfferReportController@index')->name('offer-reports.index');
        Route::get('offer-reports/{offer_report}', 'OfferReportController@show')->name('offer-reports.show');
        Route::delete('offer-reports/{offer_report}', 'OfferReportController@destroy')->name('offer-reports.delete');

        Route::get('users', 'AdminController@showUsers')->name('admin.users');
        Route::get('users/{user}', 'AdminController@showUser')->name('admin.users.show');
        Route::put('users/{user}', 'AdminController@updateUser')->name('admin.users.update');
        Route::delete('users/{user}', 'AdminController@deleteUser')->name('offer.users.delete');
        Route::get('offers', 'AdminController@viewOffers')->name('admin.offers');
        Route::get('offers/{offer}', 'AdminController@viewOffer')->name('admin.offers.show');
        Route::delete('offers/{offer}', 'AdminController@deleteOffer')->name('offer.offers.delete');

    });
});

Route::group(['middleware' => ['web']], function () {
    Route::get('/login/facebook', 'SocialAuthFacebookController@redirectToProvider');
    Route::get('/login/facebook/callback', 'SocialAuthFacebookController@handleProviderCallback');
});

