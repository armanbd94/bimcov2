@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom gutter-b">
            <div class="card-header flex-wrap py-5">
                <div class="card-title">
                    <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                </div>
                <div class="card-toolbar">
                    <button type="button" id="print-report" class="btn btn-primary btn-sm font-weight-bolder"> 
                        <i class="fas fa-print"></i> Print</button>

                </div>
            </div>

        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div id="report" class="col-md-12">
                        <style>
                            body,html {
                                background: #fff !important;
                                -webkit-print-color-adjust: exact !important;
                            }

                            .report-invoice {
                                /* position: relative; */
                                background: #fff !important;
                                /* min-height: 680px; */
                            }

                            .report-invoice header {
                                padding: 10px 0;
                                margin-bottom: 20px;
                                border-bottom: 1px solid #036;
                            }

                            .report-invoice .company-details {
                                text-align: right
                            }

                            .report-invoice .company-details .name {
                                margin-top: 0;
                                margin-bottom: 0;
                            }

                            .report-invoice .contacts {
                                margin-bottom: 20px;
                            }

                            .report-invoice .report-invoice-to {
                                text-align: left;
                            }

                            .report-invoice .report-invoice-to .to {
                                margin-top: 0;
                                margin-bottom: 0;
                            }

                            .report-invoice .report-invoice-details {
                                text-align: right;
                            }

                            .report-invoice .report-invoice-details .report-invoice-id {
                                margin-top: 0;
                                color: #036;
                            }

                            .report-invoice main {
                                padding-bottom: 50px
                            }

                            .report-invoice main .thanks {
                                margin-top: -100px;
                                font-size: 2em;
                                margin-bottom: 50px;
                            }

                            .report-invoice main .notices {
                                padding-left: 6px;
                                border-left: 6px solid #036;
                            }

                            .report-invoice table {
                                width: 100%;
                                border-collapse: collapse;
                                border-spacing: 0;
                                margin-bottom: 20px;
                            }

                            .report-invoice table th {
                                background: #036;
                                color: #fff;
                                padding: 15px;
                                border-bottom: 1px solid #fff
                            }

                            .report-invoice table td {
                                padding: 10px;
                                border-bottom: 1px solid #EBEDF3;
                            }

                            .report-invoice table th {
                                white-space: nowrap;
                            }

                            .report-invoice table td h3 {
                                margin: 0;
                                color: #036;
                            }

                            .report-invoice table .qty {
                                text-align: center;
                            }

                            .report-invoice table .price,
                            .report-invoice table .discount,
                            .report-invoice table .tax,
                            .report-invoice table .total {
                                text-align: right;
                            }

                            .report-invoice table .no {
                                color: #fff;
                                background: #036
                            }

                            .report-invoice table .total {
                                background: #036;
                                color: #fff
                            }

                            /* .report-invoice table tbody tr:last-child td {
                                border: none
                            } */

                            .report-invoice table tfoot td {
                                background: 0 0;
                                border-bottom: none;
                                white-space: nowrap;
                                text-align: right;
                                padding: 10px 20px;
                                border: 1px solid #aaa;
                                font-weight: bold;
                            }

                            .report-invoice table tfoot tr:first-child td {
                                border-top: none
                            }

                            .no-bottom-border tr td{
                                border-bottom: 0 !important;
                            }

                            /* .report-invoice table tfoot tr:last-child td {
                                color: #036;
                                border-top: 1px solid #036
                            } */

                            .report-invoice table tfoot tr td:first-child {
                                border: none
                            }

                            .report-invoice footer {
                                width: 100%;
                                text-align: center;
                                color: #777;
                                border-top: 1px solid #aaa;
                                padding: 8px 0
                            }

                            .report-invoice a {
                                content: none !important;
                                text-decoration: none !important;
                                color: #036 !important;
                            }

                            .page-header,
                            .page-header-space {
                                height: 100px;
                            }

                            .page-footer,
                            .page-footer-space {
                                height: 20px;

                            }

                            .page-footer {
                                position: fixed;
                                bottom: 0;
                                width: 100%;
                                text-align: center;
                                color: #777;
                                border-top: 1px solid #aaa;
                                padding: 8px 0
                            }

                            .page-header {
                                position: fixed;
                                top: 0mm;
                                width: 100%;
                                border-bottom: 1px solid black;
                            }

                            .page {
                                page-break-after: always;
                            }
                            .dashed-border{
                                width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
                            }

                            @media screen {
                                .no_screen {display: none;}
                                .no_print {display: block;}
                                thead {display: table-header-group;} 
                                tfoot {display: table-footer-group;}
                                button {display: none;}
                                body {margin: 0;}
                            }

                            @media print {

                                body,
                                html {
                                    /* background: #fff !important; */
                                    -webkit-print-color-adjust: exact !important;
                                    font-family: sans-serif;
                                    /* font-size: 12px !important; */
                                    margin-bottom: 100px !important;
                                }

                                .m-0 {
                                    margin: 0 !important;
                                }

                                h1,
                                h2,
                                h3,
                                h4,
                                h5,
                                h6 {
                                    margin: 0 !important;
                                }

                                .no_screen {
                                    display: block !important;
                                }

                                .no_print {
                                    display: none;
                                }

                                a {
                                    content: none !important;
                                    text-decoration: none !important;
                                    color: #036 !important;
                                }

                                .text-center {
                                    text-align: center !important;
                                }

                                .text-left {
                                    text-align: left !important;
                                }

                                .text-right {
                                    text-align: right !important;
                                }

                                .float-left {
                                    float: left !important;
                                }

                                .float-right {
                                    float: right !important;
                                }

                                .text-bold {
                                    font-weight: bold !important;
                                }

                                .report-invoice {
                                    /* font-size: 11px!important; */
                                    overflow: hidden !important;
                                    background: #fff !important;
                                    margin-bottom: 100px !important;
                                }

                                .report-invoice footer {
                                    position: absolute;
                                    bottom: 0;
                                    left: 0;
                                    /* page-break-after: always */
                                }

                                /* .report-invoice>div:last-child {
                                    page-break-before: always
                                } */
                                .hidden-print {
                                    display: none !important;
                                }
                                .dashed-border{
                                    width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
                                }
                            }

                            @page {
                                /* size: auto; */
                                margin: 5mm 5mm;

                            }
                        </style>
                        <div class="report-invoice overflow-auto">
                            <table>
                                <tr>
                                    <td class="text-center">
                                        <h2 class="name m-0" style="text-transform: uppercase;"><b>{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</b></h2>
                                        @if(config('settings.contact_no'))<p style="font-weight: normal;margin:0;"><b>Contact No.: </b>{{ config('settings.contact_no') }}, @if(config('settings.email'))<b>Email: </b>{{ config('settings.email') }}@endif</p>@endif
                                        @if(config('settings.address'))<p style="font-weight: normal;margin:0;">{{ config('settings.address') }}</p>@endif
                                        <p style="font-weight: normal;margin:0;"><b>Date: </b>{{ date('d-M-Y') }}</p>
                                        <div style="width: 250px;background:#036;color:white;font-weight:bolder;margin:5px auto 0 auto;padding: 5px 0;
                                        border-radius: 15px;text-align:center;">Today Summary Report</div>
                                    </td>
                                </tr>
                            </table>
                            <!-- Start :: Material Purchase -->
                            <div style="width: 200px;background:#036;color:white;font-weight:bolder;margin:5px auto 0 auto;padding: 5px 0;
                                        border-radius: 15px;text-align:center;">Material Purchase</div>
                            <table class="table table-bordered" style="margin-top: 10px;">
                                <thead class="bg-primary">
                                    <th class="text-center">Sl</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Memo No.</th>
                                    <th>Purchased By</th>
                                    <th>Supplier Name</th>
                                    <th class="text-right">Total Amount</th>
                                </thead>
                                <tbody>
                                    @php $total_material_purchase_amount = 0; @endphp
                                    @if (!$purchases->isEmpty())
                                        @foreach ($purchases as $key => $purchase)
                                            <tr>
                                                <td class="text-center">{{ $key+1 }}</td>
                                                <td class="text-center">{{ date(config('settings.date_format',strtotime($purchase->purchase_date))) }}</td>
                                                <td class="text-center">{{ $purchase->invoice_no }}</td>
                                                <td class="text-left">{{ $purchase->created_by }}</td>
                                                <td class="text-left">{{ $purchase->supplier->name }}</td>
                                                <td class="text-right">{{ number_format($purchase->grand_total,2) }}</td>
                                            </tr>
                                            @php $total_material_purchase_amount += $purchase->grand_total; @endphp
                                        @endforeach
                                    @else
                                    <tr><td colspan="6" class="text-center text-danger"><b>No Data Found</b></td></tr>
                                    @endif
                                </tbody>
                                <tfoot>
                                    <thead class="bg-primary">
                                        <th colspan="5" class="text-right">Total</th>
                                        <th class="text-right total-product-cost" > {{ number_format($total_material_purchase_amount,2) }}Tk</th>
                                    </thead>
                                </tfoot>
                            </table>
                            <!-- End :: Material Purchase -->

                            <!-- Start :: Product Sale -->
                            <div style="width: 200px;background:#036;color:white;font-weight:bolder;margin:30px auto 0 auto;padding: 5px 0;
                                        border-radius: 15px;text-align:center;">Product Sale</div>
                            <table class="table table-bordered" style="margin-top: 10px;">
                                <thead class="bg-primary">
                                    <th class="text-center">Sl</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Invoice No.</th>
                                    <th>Sold By</th>
                                    <th>Customer Name</th>
                                    <th class="text-right">Total Amount</th>
                                </thead>
                                <tbody>
                                    @php $total_product_sold_amount = 0; @endphp
                                    @if (!$sales->isEmpty())
                                        @foreach ($sales as $key => $sale)
                                            @if($sale->type == 1)
                                            <tr>
                                                <td class="text-center">{{ $key+1 }}</td>
                                                <td class="text-center">{{ date(config('settings.date_format',strtotime($sale->sale_date))) }}</td>
                                                <td class="text-center">{{ $sale->invoice_no }}</td>
                                                <td class="text-left">{{ $sale->created_by }}</td>
                                                <td class="text-left">{{ $sale->customer->name }}</td>
                                                <td class="text-right">{{ number_format($sale->grand_total,2) }}</td>
                                            </tr>
                                            @php $total_product_sold_amount += $sale->grand_total; @endphp
                                            @endif
                                        @endforeach
                                    @else
                                    <tr><td colspan="6" class="text-center text-danger"><b>No Data Found</b></td></tr>
                                    @endif
                                </tbody>
                                <tfoot>
                                    <thead class="bg-primary">
                                        <th colspan="5" class="text-right">Total</th>
                                        <th class="text-right total-product-cost" > {{ number_format($total_product_sold_amount,2) }}Tk</th>
                                    </thead>
                                </tfoot>
                            </table>
                            <!-- End :: Product Sale -->

 
                        </div>
                    </div>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>

@endsection

@push('scripts')
<script src="js/jquery.printarea.js"></script>
<script src="js/knockout-3.4.2.js"></script>
<script src="js/daterangepicker.min.js"></script>
<script>
$(document).on('click','#print-report',function(){
    var mode = 'iframe'; // popup
    var close = mode == "popup";
    var options = {
        mode: mode,
        popClose: close
    };
    $("#report").printArea(options);
});

</script>
@endpush