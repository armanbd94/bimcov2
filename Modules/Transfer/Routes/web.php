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
    Route::get('transfer', 'TransferController@index')->name('transfer');
    Route::group(['prefix' => 'transfer', 'as'=>'transfer.'], function () {
        Route::get('add', 'TransferController@create')->name('add');
        Route::post('datatable-data', 'TransferController@get_datatable_data')->name('datatable.data');
        Route::post('store', 'TransferController@store')->name('store');
        Route::post('update', 'TransferController@update')->name('update');
        Route::get('edit/{id}', 'TransferController@edit')->name('edit');
        Route::get('view/{id}', 'TransferController@show')->name('view');
        Route::post('delete', 'TransferController@delete')->name('delete');
        Route::post('bulk-delete', 'TransferController@bulk_delete')->name('bulk.delete');
    });

    Route::post('material-warehouse-search', 'TransferMaterialController@material_autocomplete_search');
    Route::post('transfer-material-search', 'TransferMaterialController@material_search')->name('transfer.material.search');
});
