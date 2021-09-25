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

Route::get('material-stock-ledger', 'MaterialStockLedgerController@index')->name('material.stock.ledger');
Route::post('material-stock-ledger/data', 'MaterialStockLedgerController@get_datatable_data')->name('material.stock.ledger.data');

Route::get('finish-goods-ledger', 'FinishedGoodsStockLedgerController@index')->name('finish.goods.ledger');
Route::post('finish-goods-ledger/data', 'FinishedGoodsStockLedgerController@get_datatable_data')->name('finish.goods.ledger.data');

