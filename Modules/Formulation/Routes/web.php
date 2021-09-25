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
    Route::get('formulation', 'FormulationController@index')->name('formulation');
    Route::group(['prefix' => 'formulation', 'as'=>'formulation.'], function () {
        Route::get('add', 'FormulationController@create')->name('add');
        Route::post('datatable-data', 'FormulationController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'FormulationController@store_or_update_data')->name('store.or.update');
        Route::get('edit/{id}', 'FormulationController@edit')->name('edit');
        Route::post('view ', 'FormulationController@show')->name('view');
        Route::post('delete', 'FormulationController@delete')->name('delete');
        Route::post('bulk-delete', 'FormulationController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'FormulationController@change_status')->name('change.status');
        Route::post('generate-number', 'FormulationController@generateNumber')->name('generate.number');
    });
});
