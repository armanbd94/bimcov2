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
    Route::get('finish-goods-stock', 'ProductStockController@index')->name('finish.goods.stock');
    Route::group(['prefix' => 'product-stock', 'as'=>'product.stock.'], function () {
        Route::post('datatable-data', 'ProductStockController@get_product_stock_data')->name('datatable.data');
    });
    Route::post('stock/product-search', 'ProductStockController@product_search')->name('stock.product.search');
    //Material Routes
    Route::get('material-stock', 'MaterialStockController@index')->name('material.stock');
    Route::group(['prefix' => 'material-stock', 'as'=>'material.stock.'], function () {
        Route::post('datatable-data', 'MaterialStockController@get_material_stock_data')->name('datatable.data');
    });
    Route::post('stock/material-search', 'MaterialStockController@material_search')->name('stock.material.search');
});
