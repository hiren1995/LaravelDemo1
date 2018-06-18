<?php

use Illuminate\Http\Request;



use App\NewsList;


use App\Http\Resources\NewsList as NewsResource;





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


// routes for api are made here

//bring the List of News

//Route::get('/newsList','NewsListController@index');

//add News
Route::post('/addNews','NewsListController@store');

//update News
Route::put('/updateNews','NewsListController@store');

//delete News

Route::delete('/deleteNews','NewsListController@destroy');



Route::get('newsList',function(){

	$firstnews = NewsList::all();


	return new NewsResource($firstnews);

});