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
                                <input type="hidden" name="has_opening_stock" id="has_opening_stock" value="2"/>
        
                                <x-form.textbox labelName="Product Name" name="name" required="required" col="col-md-4" placeholder="Enter product name"/>
                                
                                <div class="col-md-4 form-group required">
                                    <label for="code">Code</label>
                                    <div class="input-group" id="code_section">
                                        <input type="text" class="form-control" name="code" id="code">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-primary" id="generate-code" onclick="generateCode()"  data-toggle="tooltip" data-theme="dark" title="Generate Code"
                                            style="border-top-right-radius: 0.42rem;border-bottom-right-radius: 0.42rem;border:0;cursor: pointer;">
                                                <i class="fas fa-retweet text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
        
                                <x-form.selectbox labelName="Category" name="category_id" required="required" col="col-md-4" class="selectpicker">
                                    @if (!$categories->isEmpty())
                                        @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
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
                                                <option value="{{ $unit->id }}">{{ $unit->unit_name.' ('.$unit->unit_code.')' }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
        
                                <div class="form-group col-md-4 required">
                                    <label for="code">Sale Unit</label>
                                    <select name="sale_unit_id" id="sale_unit_id"  class="form-control selectpicker" data-live-search="true"  data-live-search-placeholder="Search"></select>
                                </div>
        
                                {{-- <x-form.textbox labelName="Product Cost" name="cost" required="required" col="col-md-4" placeholder="Enter product cost"/> --}}
                                <x-form.textbox labelName="Product Price" name="price" required="required" col="col-md-4" placeholder="Enter product price"/>
                                <x-form.textbox labelName="Opening Stock Quantity" name="opening_stock_qty" required="required"  col="col-md-4 d-none product-qty" placeholder="0"/>
                                <x-form.textbox labelName="Opening Cost" name="opening_cost" required="required"  col="col-md-4 d-none product-cost" placeholder="0"/>
                                <x-form.selectbox labelName="Opening Stock Warehouse" name="opening_warehouse_id" required="required"  col="col-md-4 opening-warehouse-id d-none" class="selectpicker" onchange="populate_unit(this.value)">
                                    @if (!$warehouses->isEmpty())
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>
                                <x-form.textbox labelName="Alert Quantity" name="alert_quantity"  col="col-md-4" placeholder="Enter product alerty qty"/>
        
                                <div class="col-md-4 form-group">
                                    <label for="tax_id">Product Tax</label>
                                    <select name="tax_id" id="tax_id" required="required" class="form-control selectpicker">
                                        <option value="0" selected>No Tax</option>
                                        @if (!$taxes->isEmpty())
                                            @foreach ($taxes as $tax)
                                                <option value="{{ $tax->id }}"  {{ isset($product) ? (($product->tax_id == $tax->id) ? 'selected' : '')  : '' }}>{{ $tax->name }}</option>
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
                                    <textarea class="form-control" name="description" id="description"></textarea>
                                </div>
                                <div class="form-group col-md-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="opening_stock">
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
                            <button type="button" class="btn btn-danger btn-sm mr-3">Reset</button>
                            <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data(1)">Save</button>
                            <button type="button" class="btn btn-success btn-sm" id="add-another-btn" onclick="store_data(2)">Save And Add Another</button>
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
generateCode();
function generateCode(){
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
}
function store_data(btn){
    let form = document.getElementById('store_or_update_form');
    let formData = new FormData(form);
    let url = "{{route('finish.goods.store.or.update')}}";
    let id = $('#update_id').val();
    let method;
    if (id) {
        method = 'update';
    } else {
        method = 'add';
    }
    $.ajax({
        url: url,
        type: "POST",
        data: formData,
        dataType: "JSON",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function(){
            btn == 1 ? $('#save-btn').addClass('spinner spinner-white spinner-right') : $('#add-another-btn').addClass('spinner spinner-white spinner-right');
        },
        complete: function(){
            btn == 1 ? $('#save-btn').removeClass('spinner spinner-white spinner-right') : $('#add-another-btn').removeClass('spinner spinner-white spinner-right');
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
                    if(key == 'code'){
                        $('#store_or_update_form #' + key).parents('.form-group').append(
                        '<small class="error text-danger">' + value + '</small>');
                    }else{
                        $('#store_or_update_form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');
                    }
                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success') {
                    if(btn == 1){
                        window.location.replace("{{ route('finish.goods') }}");
                    }else{
                        window.location.replace("{{ route('finish.goods.add') }}");
                    }
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