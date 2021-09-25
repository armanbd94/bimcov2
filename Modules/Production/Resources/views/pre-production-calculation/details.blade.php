@extends('layouts.app')

@section('title', $page_title)


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
                    <!--begin::Button-->
                    <button type="button" class="btn btn-primary btn-sm mr-3" id="print-invoice"> <i class="fas fa-print"></i> Print</button>
                    <a href="{{ url('pre-production-list') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <div class="card-body">
                <div id="invoice">
                    <style>
                        body,html {
                            background: #fff !important;
                            -webkit-print-color-adjust: exact !important;
                        }

                        .invoice {
                            /* position: relative; */
                            background: #fff !important;
                            /* min-height: 680px; */
                        }

                        .invoice header {
                            padding: 10px 0;
                            margin-bottom: 20px;
                            border-bottom: 1px solid #036;
                        }

                        .invoice .company-details {
                            text-align: right
                        }

                        .invoice .company-details .name {
                            margin-top: 0;
                            margin-bottom: 0;
                        }

                        .invoice .contacts {
                            margin-bottom: 20px;
                        }

                        .invoice .invoice-to {
                            text-align: left;
                        }

                        .invoice .invoice-to .to {
                            margin-top: 0;
                            margin-bottom: 0;
                        }

                        .invoice .invoice-details {
                            text-align: right;
                        }

                        .invoice .invoice-details .invoice-id {
                            margin-top: 0;
                            color: #036;
                        }

                        .invoice main {
                            padding-bottom: 50px
                        }

                        .invoice main .thanks {
                            margin-top: -100px;
                            font-size: 2em;
                            margin-bottom: 50px;
                        }

                        .invoice main .notices {
                            padding-left: 6px;
                            border-left: 6px solid #036;
                        }

                        .invoice table {
                            width: 100%;
                            border-collapse: collapse;
                            border-spacing: 0;
                            margin-bottom: 20px;
                        }
                        .bg-primary {
                            background-color: #034d97 !important;
                        }
                        .font-weight-bolder {
                            font-weight: 600 !important;
                        }
                        .invoice table th {
                            /* background: #036; */
                            color: #fff;
                            padding: 15px;
                            /* border-bottom: 1px solid #fff */
                        }

                        .invoice table td {
                            padding: 15px;
                            /* border-bottom: 1px solid #fff */
                        }

                        .invoice table th {
                            white-space: nowrap;
                        }

                        .invoice table td h3 {
                            margin: 0;
                            color: #036;
                        }

                        .invoice table .no {
                            color: #fff;
                            background: #036
                        }

                        .invoice table .total {
                            background: #036;
                            color: #fff
                        }

                        /* .invoice table tbody tr:last-child td {
                            border: none
                        } */

                        /* .invoice table tfoot td {
                            background: 0 0;
                            border-bottom: none;
                            white-space: nowrap;
                            text-align: right;
                            padding: 10px 20px;
                            /* border-top: 1px solid #aaa; */
                            font-weight: bold;
                        } */

                        /* .invoice table tfoot tr:first-child td {
                            border-top: none
                        } */

                        /* .invoice table tfoot tr:last-child td {
                            color: #036;
                            border-top: 1px solid #036
                        } */

                        /* .invoice table tfoot tr td:first-child {
                            border: none
                        } */

                        .invoice footer {
                            width: 100%;
                            text-align: center;
                            color: #777;
                            border-top: 1px solid #aaa;
                            padding: 8px 0
                        }

                        .invoice a {
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
                        .table-bordered {
    border: 1px solid #EBEDF3;
}
.table thead th {
    vertical-align: bottom;
    border-bottom: 2px solid #EBEDF3;
}
.table-bordered td {
    border: 1px solid #EBEDF3;
}
.table-title{
    width:250px;
    margin: 20px auto 10px auto;
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
                            .w-50{
                                width:50%;
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
                            .text-white{color:white !important;}
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

                            .invoice {
                                /* font-size: 11px!important; */
                                overflow: hidden !important;
                                background: #fff !important;
                                margin-bottom: 100px !important;
                            }

                            .invoice footer {
                                position: absolute;
                                bottom: 0;
                                left: 0;
                                /* page-break-after: always */
                            }
                            
                            .text-white{
                                color: white;
                            }
                            .w-100{
                                width:100% !important;
                            }
                            .bg-red{
                                background: red;
                            }
                            .table-title{
                                width: 100%;
                                margin: 0 auto;
                                padding: 10px;
                                color: black !important;
                                background: none !important;
                            }
                            /* .invoice>div:last-child {
                                page-break-before: always
                            } */
                            .hidden-print {
                                display: none !important;
                            }
                            .dashed-border{
                                width:180px;height:2px;
                                margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
                            }
                            #materialTbl{
                                font-size: 14px;
                            }
                        }

                        @page {
                            /* size: auto; */
                            margin: 5mm 5mm;

                        }
                    </style>
                    <div class="invoice">
                        <div class="row">
                            <div class="col-md-12 no_screen">
                                <table>
                                    <tr>
                                        <td class="text-center">
                                            <h2 class="name m-0" style="text-transform: uppercase;"><b>{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</b></h2>
                                            @if(config('settings.contact_no'))<p style="font-weight: normal;margin:0;"><b>Contact No.: </b>{{ config('settings.contact_no') }}, @if(config('settings.email'))<b>Email: </b>{{ config('settings.email') }}@endif</p>@endif
                                            @if(config('settings.address'))<p style="font-weight: normal;margin:0;">{{ config('settings.address') }}</p>@endif
                                            <p style="font-weight: normal;margin:0;"><b>Date: </b>{{ date('d-M-Y') }}</p>
                                        </td>
                                    </tr>
                                </table>
                                <div style="width: 100%;height:3px;border-top:1px solid #036;border-bottom:1px solid #036;"></div>
                            </div>
                            <div class="col-md-12">
                                <table class="table table-borderless table-hover" id="materialTbl">
                                    <tr>
                                        <td><b>PP Serial No</b></td> <td class="text-center"><b>:</b></td> <td class="font-weight-bolder text-dark">{{ $production->serial_no }}</td>
                                        <td><b>Finish Goods</b></td> <td class="text-center"><b>:</b></td> <td class="font-weight-bolder text-dark">{{ $production->product->name }}</td>
                                        <td><b>FG Serial No.</b></td> <td class="text-center"><b>:</b></td> <td>{{ $production->formulation->formulation_no }}</td>
                                    </tr>
                                    <tr>
                                        
                                        <td><b>Serial No.</b></td> <td class="text-center"><b>:</b></td> <td>{{ $production->formulation_id }}</td>
                                        <td><b>Date</b></td> <td class="text-center"><b>:</b></td> <td>{{ date('j-F-Y',strtotime($production->date)) }}</td>
                                        <td><b>Unit</b></td> <td class="text-center"><b>:</b></td> <td>{{ $production->unit->unit_name }}</td>
                                        
                                    </tr>
                                    <tr>
                                        <td><b>Total FG Quantity</b></td> <td class="text-center"><b>:</b></td> <td>{{ $production->total_fg_qty }}</td>
                                        <td><b>Material Warehouse</b></td> <td class="text-center"><b>:</b></td> <td>{{ $production->mwarehouse->name }}</td>
                                        <td><b>Created By</b></td> <td class="text-center"><b>:</b></td> <td>{{ $production->creator->name }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-12 py-5">
                                <table class="table table-bordered pb-5">
                                    <thead class="bg-primary">
                                        <th class="text-left">Material</th>
                                        <th class="text-center">Category</th>
                                        <th class="text-center">Unit Name</th>
                                        <th class="text-right">RM Quantity</th>
                                        <th class="text-right">RM Rate</th>
                                        <th class="text-right">RM Value</th>
                                    </thead>
                                    <tbody>
                                        @if (!$production->materials->isEmpty())
                                            @foreach ($production->materials as $key => $item)
                                            <tr>
                                                <td>{{ $item->material_code.' - '.$item->material_name }}</td>
                                                <td class="text-center">{{ $item->category->name }}</td>
                                                <td class="text-center">{{ $item->unit->unit_name }}</td>
                                                <td class="text-right">{{ $item->pivot->qty }}</td>
                                                <td class="text-right">{{ $item->pivot->rate }}</td>
                                                <td class="text-right">{{ $item->pivot->total }}</td>
                                            </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            @if (!$production->packaging_materials->isEmpty())
                            <div class="col-md-12 py-5">
                                <div class="col-md-12 text-center" style="width:100%;padding:0;">
                                    <h5 class="bg-warning text-white p-3 text-center table-title">Materials Adjustment</h5>
                                </div>
                                <table class="table table-bordered pb-5" id="material_mixing_table">
                                    <thead class="bg-primary">
                                        <th width="25%">Material</th>
                                        <th class="text-center">Category</th>
                                        <th class="text-center">Prefix</th>
                                        <th class="text-center">Unit Name</th>
                                        <th class="text-right">RM Rate</th>
                                        <th class="text-right">RM Quantity</th>
                                        <th class="text-right">RM Value</th>
                                    </thead>
                                    <tbody>
                                        
                                        @foreach ($production->packaging_materials as $key => $item)
                                        <tr>
                                            <td>{{ $item->material_code.' - '.$item->material_name }}</td>
                                            <td class="text-center">{{ $item->category->name }}</td>
                                            <td class="text-center">{{ $item->pivot->prefix }}</td>
                                            <td class="text-center">{{ $item->unit->unit_name }}</td>
                                            <td class="text-right">{{ $item->pivot->qty }}</td>
                                            <td class="text-right">{{ $item->pivot->rate }}</td>
                                            <td class="text-right">{{ $item->pivot->total }}</td>
                                        </tr>
                                        @endforeach
                                    
                                    </tbody>
                                </table>
                            </div>
                            @endif
                            <div class="col-md-12 py-5">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td width="70%" class="text-right font-weight-bolder">Total Cost</td>
                                            <td class="text-right font-weight-bolder"> {{ number_format($production->total_cost,4,'.','') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-right font-weight-bolder">Extra Cost</td>
                                            <td class="text-right font-weight-bolder"> {{ number_format($production->extra_cost,4,'.','')}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-right font-weight-bolder">Total Net Cost</td>
                                            <td class="text-right font-weight-bolder"> {{ number_format($production->total_net_cost,4,'.','')}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-right font-weight-bolder">Per Unit Cost</td>
                                            <td class="text-right font-weight-bolder"> {{ number_format($production->per_unit_cost,4,'.','')}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection

@push('scripts')
<script src="js/jquery.printarea.js"></script>
<script>
$(document).ready(function () {
    //QR Code Print
    $(document).on('click','#print-invoice',function(){
        var mode = 'iframe'; // popup
        var close = mode == "popup";
        var options = {
            mode: mode,
            popClose: close
        };
        $("#invoice").printArea(options);
    });
});

</script>
@endpush