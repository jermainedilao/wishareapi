<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/

/*Route::group(['prefix'=>''],function(){
	Route::resource('register','Auth\RegisterController');
});*/

//AUTH
Route::post('register',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'Auth\RegisterController@store']);
Route::post('login',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'Auth\AuthController@login']);
Route::get('logout',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'Auth\AuthController@logout']);

//USER
Route::get('users/{id}',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'WishareUserController@show']);
Route::put('users/{id}',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'WishareUserController@update']);
Route::get('users/{id}/search',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'WishareUserController@search']);

//WISHLIST
Route::get('users/{userid}/wishlists',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'WishlistController@index']);
Route::post('users/{userid}/wishlists',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'WishlistController@store']);
Route::put('wishlists/{wishlistid}',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'WishlistController@update']);
Route::delete('wishlists/{wishlistid}',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'WishlistController@destroy']);

//WISH
Route::get('wishlists/{wishlistid}/wishes',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'WishController@index']);
Route::post('users/{userid}/wishlists/{wishlistid}/wishes',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'WishController@store']);
Route::put('wishes/{wishid}',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'WishController@update']);
Route::delete('wishes/{wishid}',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'WishController@destroy']);
Route::get('wishes/{wishid}',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'WishController@show']);

//FRIEND
Route::get('users/{userid}/friends',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'FriendController@index']);
Route::get('users/{userid}/friends/pending',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'FriendController@getPendingFriendRequest']);
Route::post('users/{userid}/friends',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'FriendController@store']);
Route::put('users/{userid}/friends/{frienduserid}',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'FriendController@update']);
Route::delete('users/{userid}/friends/{frienduserid}',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'FriendController@destroy']);

//STREAM
Route::get('users/{userid}/stream',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'StreamController@index']);

//GRANT
Route::get('users/{userid}/grant/granted',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'GrantController@index']);
Route::get('users/{userid}/grant/given',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'GrantController@getGivenWishes']);
Route::get('users/{userid}/grant/pending',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'GrantController@getPendingGrantRequest']);
Route::put('wishes/{wishid}/grant',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'GrantController@update']);
Route::post('wishes/{wishid}/grant',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'GrantController@store']);
Route::delete('wishes/{wishid}/grant',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'GrantController@destroy']);

//Route::get('test',['prefix'=>'v1', 'uses'=>'WishController@test']);

//FAVORITE BOOKMARK
Route::get('users/{userid}/activities',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'FavoriteBookmarkController@index']);
Route::post('wishes/{wishid}/activities',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'FavoriteBookmarkController@store']);
Route::delete('wishes/{wishid}/activities',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'FavoriteBookmarkController@destroy']);
Route::get('users/{userid}/bookmarked',['prefix'=>'v1','middleware' => 'accesstoken','uses'=>'FavoriteBookmarkController@getBookmarkedWishes']);

