<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


/* Route::get('/', 'ImageController@check'); */

Route::post('/work', 'ImageController@upload');

Route::post('/work/mockup', 'ImageController@uploadMockup');

Route::post('/work/mockup1', 'ImageController@uploadMockup1');

Route::post('/work/mockup2', 'ImageController@uploadMockup2');

Route::post('/work/mockup3', 'ImageController@uploadMockup3');

Route::post('/work/mockup4', 'ImageController@uploadMockup4');

