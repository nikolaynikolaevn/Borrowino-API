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

Route::get('offer', 'OfferController@index')->name('offer.index');
Route::get('offer/{offer}', 'OfferController@show')->name('offer.show');

Route::get('/admin/userDetails', 'adminController@showUserDetails')->name('admin.showUserDetails');
Route::get('/admin/userDetails{id}', 'adminController@showUserDetails')->name('admin.showUserDetails');
Route::get('/admin/viewOffer{id}', 'adminController@viewOffer')->name('admin.viewOffer');
Route::get('/admin/viewAllOffer', 'adminController@viewAllOffer')->name('admin.viewAllOffer');
Route::delete('admin/user/{id}', 'adminController@deleteUser')->name('offer.deleteUser');
Route::delete('admin/offer/{id}', 'adminController@deleteOffer')->name('offer.deleteOffer');


Route::middleware('auth:api')->group(function () {
    Route::post('logout', 'AuthController@logout')->name('logout');

    Route::apiResource('offer', 'OfferController')->except(['index', 'show']);
});


