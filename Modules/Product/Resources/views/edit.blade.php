@extends('layouts.app')

@section('title', $page_title)

@push('styles')

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
                <form id="store_or_update_form" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-9">
                            <div class="row">
                                <input type="hidden" name="update_id" id="update_id" value="{{ $product->id }}"/>
                                <input type="hidden" name="has_opening_stock" id="has_opening_stock" value="{{ $product->has_opening_stock }}"/>
        
                                <x-form.textbox labelName="Product Name" name="name" required="required" value="{{ $product->name }}" col="col-md-4" placeholder="Enter product name"/>
                                
                                <div class="col-md-4 form-group required">
                                    <label for="code">Code</label>
                                    <input type="text" class="form-control" name="code" id="code" value="{{ $product->code }}" readonly>
                                </div>
        
                                <x-form.selectbox labelName="Category" name="category_id" required="required" col="col-md-4" class="selectpicker">
                                    @if (!$categories->isEmpty())
                                        @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>
        
                                <div class="form-group col-md-4 required">
                                    <label for="code">Unit</label>
                                    <select name="unit_id" id="unit_id"  onchange="populate_unit(this.value)" class="form-control selectpicker" data-live-search="true"  data-live-search-placeholder="Search">
                                        <option value="">Select Please</option>
                                        @if (!$units->isEmpty())
                                            @foreach ($units as $unit)
                                                @if ($unit->base_unit == null)
                                                <option value="{{ $unit->id }}" {{ $product->unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->unit_name.' ('.$unit->unit_code.')' }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
        
                                <div class="form-group col-md-4 required">
                                    <label for="code">Sale Unit</label>
                                    <select name="sale_unit_id" id="sale_unit_id"  class="form-control selectpicker" data-live-search="true"  data-live-search-placeholder="Search">
                                        @php
                                            $sale_units = \DB::table('units')->where('base_unit',$product->unit_id)
                                            ->orWhere('id',$product->unit_id)->get();
                                        @endphp
                                        @if (!$sale_units->isEmpty())
                                            @foreach ($sale_units as $unit)
                                            <option value="{{ $unit->id }}" {{ ($product->sale_unit_id == $unit->id) ? 'selected' : '' }}>{{ $unit->unit_name.' ('.$unit->unit_code.')' }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
        
                                {{-- <x-form.textbox labelName="Product Cost" name="cost" required="required" value="{{ $product->cost }}" col="col-md-4" placeholder="Enter product cost"/> --}}
                                <x-form.textbox labelName="Product Price" name="price" required="required" value="{{ $product->price }}" col="col-md-4" placeholder="Enter product price"/>
                                <div class="form-group col-md-4 {{ empty($product->opening_stock_qty) ? 'd-none' : '' }} product-qty required">
                                    <label for="opening_stock_qty">Opening Stock Quantity</label>
                                    <input type="text" class="form-control" name="opening_stock_qty" id="opening_stock_qty" value="{{ $product->opening_stock_qty }}">
                                </div>
                                <x-form.textbox labelName="Opening Cost" name="opening_cost" required="required" value="{{ $product->opening_cost }}"  col="col-md-4 {{ empty($product->opening_stock_qty) ? 'd-none' : '' }} product-cost" placeholder="0"/>
                                <div class="col-md-4 form-group opening-warehouse-id {{ empty($product->opening_warehouse_id) ? 'd-none' : '' }} required">
                                    <label for="opening_warehouse_id">Opening Stock Warehouse</label>
                                    <select name="opening_warehouse_id" id="opening_warehouse_id" required="required" class="form-control selectpicker">
                                        <option value="">Select Please</option>
                                        @if (!$warehouses->isEmpty())
                                            @foreach ($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}" {{ ($product->opening_warehouse_id == $warehouse->id) ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                              
                                <x-form.textbox labelName="Alert Quantity" name="alert_quantity"  col="col-md-4" value="{{ $product->alert_quantity }}" placeholder="Enter product alerty qty"/>
        
                                <div class="col-md-4 form-group">
                                    <label for="tax_id">Product Tax</label>
                                    <select name="tax_id" id="tax_id" required="required" class="form-control selectpicker">
                                        <option value="0" {{ ($product->tax_id == '') ? 'selected' : '' }}>No Tax</option>
                                        @if (!$taxes->isEmpty())
                                            @foreach ($taxes as $tax)
                                                <option value="{{ $tax->id }}"  {{ ($product->tax_id == $tax->id) ? 'selected' : '' }}>{{ $tax->name }}</option>
                                            @endforeach 
                                        @endif
                                    </select>
                                </div>
        
                                <div class="col-md-4 form-group">
                                    <label for="tax_method">Tax Method<span class="text-danger">*</span> <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top"
                                        data-theme="dark" title="Exclusive: Poduct price = Actual product price + Tax. Inclusive: Actual product price = Product price - Tax"></i></label>
                                    <select name="tax_method" id="tax_method" required="required" class="form-control selectpicker">
                                    @foreach (TAX_METHOD as $key => $value)
                                        <option value="{{ $key }}" 
                                        @if(isset($product)) @if($product->tax_method == $key) {{ 'selected' }} @endif @else  @if($key == 1){{ 'selected' }} @endif @endif>{{ $value }}</option>
                                    @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group col-md-12">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" name="description" id="description">{{ $product->description }}</textarea>
                                </div>
                                <div class="form-group col-md-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="opening_stock" {{ $product->has_opening_stock == 1 ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="opening_stock">This product has opening stock</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="row">
                                <div class="form-group col-md-12 mb-0">
                                    <label for="logo" class="form-control-label">Product Image</label>
                                    <div class="col=md-12 px-0  text-center">
                                        <div id="image">
                        
                                        </div>
                                    </div>
                                    <input type="hidden" name="old_image" id="old_image">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12 text-left px-0">
                            <a href="{{ route('finish.goods') }}" class="btn btn-danger btn-sm mr-3">Cancel</a>
                            <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection

@push('scripts')
<script src="js/spartan-multi-image-picker-min.js"></script>
<script>
$(document).ready(function () {

    $("#image").spartanMultiImagePicker({
        fieldName:        'image',
        maxCount: 1,
        rowHeight:        '200px',
        groupClassName:   'col-md-12 col-sm-12 col-xs-12',
        maxFileSize:      '',
        dropFileLabel : "Drop Here",
        allowedExt: 'png|jpg|jpeg',
        onExtensionErr : function(index, file){
            Swal.fire({icon: 'error',title: 'Oops...',text: 'Only png,jpg,jpeg file format allowed!'});
        },

    });

    $("input[name='image']").prop('required',true);

    $('.remove-files').on('click', function(){
        $(this).parents(".col-md-12").remove();
    });

    @if(!empty($product->image))
            $('#product_image img.spartan_image_placeholder').css('display','none');
            $('#product_image .spartan_remove_row').css('display','none');
            $('#product_image .img_').css('display','block');
            $('#product_image .img_').attr('src',"{{ asset('storage/'.PRODUCT_IMAGE_PATH.$product->image)}}");
        @endif

    
    //Generate Material Code
    $(document).on('click','#generate-code',function(){
        $.ajax({
            url: "{{ route('finish.goods.generate.code') }}",
            type: "GET",
            dataType: "JSON",
            beforeSend: function(){
                $('#generate-code').addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#generate-code').removeClass('spinner spinner-white spinner-right');
            },
            success: function (data) {
                data ? $('#store_or_update_form #code').val(data) : $('#store_or_update_form #code').val('');
            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    });

    $(document).on('change','#opening_stock', function(){
        if($(this).is(':checked')) {
            $('#has_opening_stock').val(1);
            $('.product-qty,.product-cost,.opening-warehouse-id').removeClass('d-none');
            $('#opening_stock_qty,#opening_cost,#opening_warehouse_id').val('');
            $('#store_or_update_form .selectpicker').selectpicker('refresh');
        }else{
            $('#has_opening_stock').val(2);
            $('#opening_stock_qty,#opening_cost,#opening_warehouse_id').val('');
            $('#store_or_update_form .selectpicker').selectpicker('refresh');
            $('.product-qty,.product-cost,.opening-warehouse-id').addClass('d-none');
        }
    });


});

function populate_unit(unit_id)
{
    $.ajax({
        url:"{{ url('populate-unit') }}/"+unit_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            $('#sale_unit_id').empty();
            $.each(data, function(key, value) {
                $('#sale_unit_id').append('<option value="'+ key +'">'+ value +'</option>');
            });
            $('#sale_unit_id.selectpicker').selectpicker('refresh');
        },
    });
}

function store_data(){
    let form = document.getElementById('store_or_update_form');
    let formData = new FormData(form);
    let url = "{{route('finish.goods.store.or.update')}}";

    $.ajax({
        url: url,
        type: "POST",
        data: formData,
        dataType: "JSON",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function(){
           $('#save-btn').addClass('spinner spinner-white spinner-right');
        },
        complete: function(){
           $('#save-btn').removeClass('spinner spinner-white spinner-right');
        },
        success: function (data) {
            $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
            $('#store_or_update_form').find('.error').remove();
            if (data.status == false) {
                $.each(data.errors, function (key, value){
                    var key = key.split('.').join('_');
                    $('#store_or_update_form input#' + key).addClass('is-invalid');
                    $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                    $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                    $('#store_or_update_form #' + key).parent().append(
                    '<small class="error text-danger">' + value + '</small>');
                    
                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success') {
                        window.location.replace("{{ route('finish.goods') }}");
                }
            }
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}
</script>
@endpush