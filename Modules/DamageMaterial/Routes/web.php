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
    //Damage Material Routes
    Route::get('damage-material', 'DamageMaterialController@index')->name('damage.material');
    Route::group(['prefix' => 'damage-material', 'as'=>'damage.material.'], function () {
        Route::post('datatable-data', 'DamageMaterialController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'DamageMaterialController@store_or_update_data')->name('store.or.update');
        Route::post('view', 'DamageMaterialController@show')->name('view');
        Route::post('edit', 'DamageMaterialController@edit')->name('edit');
        Route::post('delete', 'DamageMaterialController@delete')->name('delete');
        Route::post('bulk-delete', 'DamageMaterialController@bulk_delete')->name('bulk.delete');
    });
});
