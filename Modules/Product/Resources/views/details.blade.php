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
                    <a href="{{ route('finish.goods') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-9">
                        <table class="table table-borderless table-hover">
                            <tr><td width="25%"><b>Name</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ $product->name }}</td></tr>
                            <tr><td width="25%"><b>Code</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ $product->code }}</td></tr>
                            <tr><td width="25%"><b>Category</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ $product->category->name }}</td></tr>
                            <tr><td width="25%"><b>Stock Unit</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ $product->unit->unit_name }}</td></tr>
                            <tr><td width="25%"><b>Sale Unit</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ $product->sale_unit->unit_name }}</td></tr>
                            <tr><td width="25%"><b>Cost</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ number_format($product->cost,2) }}</td></tr>
                            <tr><td width="25%"><b>Price</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ number_format($product->price,2) }}</td></tr>
                            <tr><td width="25%"><b>Stock Qunatity</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ number_format($product->qty,2) }}</td></tr>
                            @if($product->has_opening_stock == 1)
                            <tr><td width="25%"><b>Opening Stock Qunatity</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ number_format($product->opening_stock_qty,2) }}</td></tr>
                            <tr><td width="25%"><b>Opening Warehouse</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ $product->opening_warehouse->name }}</td></tr>
                            @endif
                            <tr><td width="25%"><b>Tax</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ $product->tax->rate }}%</td></tr>
                            <tr><td width="25%"><b>Tax Method</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ TAX_METHOD[$product->tax_method] }}</td></tr>
                            <tr><td width="25%"><b>Alert Quantity</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ $product->alert_quantity }}</td></tr>
                            <tr><td width="25%"><b>Description</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ $product->description }}</td></tr>
                        </table>
                    </div>
                    @if (!empty($product->image))
                    <div class="col-md-3">
                        <img src="{{ asset('storage/'.PRODUCT_IMAGE_PATH.$product->image) }}" alt="{{ $product->name }}" style="width: 100%;">
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection
