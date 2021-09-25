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
    //Closing Route
    Route::get('closing', 'ClosingReportController@index')->name('closing');
    Route::post('closing-data', 'ClosingReportController@closing_data')->name('closing.data');
    Route::post('closing/store', 'ClosingReportController@store')->name('closing.store');

    //Closing Report Route
    Route::get('closing-report', 'ClosingReportController@report')->name('closing.report');
    Route::post('closing-report/datatable-data', 'ClosingReportController@get_datatable_data')->name('closing.report.datatable.data');
    
    //Todays Purchase Report Route
    Route::get('todays-purchase-report', 'TodaysPurchaseReportController@index')->name('todays.purchase.report');
    Route::post('todays-purchase-report/datatable-data', 'TodaysPurchaseReportController@get_datatable_data')->name('todays.purchase.report.datatable.data');
    
    //Purchase Report Route
    Route::get('purchase-report', 'PurchaseReportController@index')->name('purchase.report');
    Route::post('purchase-report/datatable-data', 'PurchaseReportController@get_datatable_data')->name('purchase.report.datatable.data');
    
    //Todays Sales Report Route
    Route::get('todays-sales-report', 'TodaysSalesReportController@index')->name('todays.sales.report');
    Route::post('todays-sales-report/datatable-data', 'TodaysSalesReportController@get_datatable_data')->name('todays.sales.report.datatable.data');
    
    //Sales Report Route
    Route::get('sales-report', 'SalesReportController@index')->name('sales.report');
    Route::post('sales-report/datatable-data', 'SalesReportController@get_datatable_data')->name('sales.report.datatable.data');
    
    //Todays Customer Receipt Route
    Route::get('todays-customer-receipt', 'TodaysCustomerReceiptController@index')->name('todays.customer.receipt');
    Route::post('todays-customer-receipt/datatable-data', 'TodaysCustomerReceiptController@get_datatable_data')->name('todays.customer.receipt.datatable.data');

      //Due Report Route
    Route::get('due-report', 'DueReportController@index')->name('due.report');
    Route::post('due-report/datatable-data', 'DueReportController@get_datatable_data')->name('due.report.datatable.data');

    //Product Wise Sales Report Route
    Route::get('product-wise-sales-report', 'ProductWiseSalesReportController@index')->name('product.wise.sales.report');
    Route::post('product-wise-sales-report/datatable-data', 'ProductWiseSalesReportController@get_datatable_data')->name('product.wise.sales.report.datatable.data');
    
    //Product Wise Profit Report Route
    Route::get('product-wise-profit-report', 'ProductWiseProfitReportController@index')->name('product.wise.profit.report');
    Route::post('product-wise-profit-report/datatable-data', 'ProductWiseProfitReportController@get_datatable_data')->name('product.wise.profit.report.datatable.data');

    //Stock Alert Report Route
    Route::get('stock-alert-report', 'StockAlertController@index')->name('stock.alert.report');
    Route::post('stock-alert-report/datatable-data', 'StockAlertController@get_datatable_data')->name('stock.alert.report.datatable.data');
 
    //Material Alert Report Route
    Route::get('material-stock-alert-report', 'MaterialStockAlertController@index')->name('material.stock.alert.report');
    Route::post('material-stock-alert-report/datatable-data', 'MaterialStockAlertController@get_datatable_data')->name('material.stock.alert.report.datatable.data');
 
     //Summary Report
     Route::get('summary-report', 'SummaryReportController@index')->name('summary.report');
});
