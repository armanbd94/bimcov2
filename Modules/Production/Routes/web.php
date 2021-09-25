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

    
    
    Route::post('formulation-data', 'FormulationDataController@index');
    Route::post('formulation-materials', 'FormulationDataController@formulation_materials');
    Route::post('pre-production-formulation-materials', 'FormulationDataController@pre_production_formulation_materials');

    //Pre Production Routes
    Route::get('pre-production-list', 'PreProductionCalculationController@index');
    Route::get('pre-production-calculation', 'PreProductionCalculationController@create')->name('pre.production.calculation');
    Route::group(['prefix' => 'pre-production', 'as'=>'pre.production.'], function () {
        Route::post('datatable-data', 'PreProductionCalculationController@get_datatable_data')->name('datatable.data');
        Route::post('store', 'PreProductionCalculationController@store')->name('store');
        Route::post('update', 'PreProductionCalculationController@update')->name('update');
        Route::get('edit/{id}', 'PreProductionCalculationController@edit')->name('edit');
        Route::get('view/{id}', 'PreProductionCalculationController@show')->name('view');
        Route::post('delete', 'PreProductionCalculationController@delete')->name('delete');
        Route::post('bulk-delete', 'PreProductionCalculationController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'PreProductionCalculationController@change_status')->name('change.status');
    });
    Route::post('pre-production-check-material-stock', 'PreProductionCalculationController@check_material_stock');
    Route::post('pre-production-product-mixing/store', 'PreProductionCalculationController@pre_production_product_mixing_store');
    Route::get('pre-production-to-product-mixing/{id}', 'PreProductionCalculationController@pre_production_to_product_mixing');
    

    //Product Mixing Routes
    Route::get('product-mixing', 'ProductionController@index')->name('product.mixing');
    Route::group(['prefix' => 'product-mixing', 'as'=>'product.mixing.'], function () {
        Route::get('add', 'ProductionController@create')->name('add');
        Route::post('datatable-data', 'ProductionController@get_datatable_data')->name('datatable.data');
        Route::post('store', 'ProductionController@store')->name('store');
        Route::post('update', 'ProductionController@update')->name('update');
        Route::get('edit/{id}', 'ProductionController@edit')->name('edit');
        Route::get('view/{id}', 'ProductionController@show')->name('view');
        Route::post('delete', 'ProductionController@delete')->name('delete');
        Route::post('bulk-delete', 'ProductionController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'ProductionController@change_status')->name('change.status');
        Route::post('change-production-status', 'ProductionController@change_production_status')->name('change.production.status');
        Route::post('generate-number', 'ProductionController@generateNumber')->name('generate.number');
    });
    Route::get('generate-batch-no/{product_id}', 'ProductionController@generate_batch_no')->name('generate.batch.no');
    Route::post('production-check-material-stock', 'ProductionController@check_material_stock');
    Route::post('product-mixing-formulation-data', 'ProductionController@formulationData');


});