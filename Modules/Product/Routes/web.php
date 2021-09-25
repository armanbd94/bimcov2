<?php

use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => ['auth']], function () {
    //Product Routes
    Route::get('finish-goods', 'ProductController@index')->name('finish.goods');
    Route::group(['prefix' => 'finish-goods', 'as'=>'finish.goods.'], function () {
        Route::get('add', 'ProductController@create')->name('add');
        Route::post('datatable-data', 'ProductController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'ProductController@store_or_update_data')->name('store.or.update');
        Route::get('edit/{id}', 'ProductController@edit')->name('edit');
        Route::get('view/{id}', 'ProductController@show');
        Route::post('delete', 'ProductController@delete')->name('delete');
        Route::post('bulk-delete', 'ProductController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'ProductController@change_status')->name('change.status');
        Route::get('generate-code', 'ProductController@generateProductCode')->name('generate.code');
    });
});
