@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
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
                    <a href="{{ route('formulation') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <div class="card-body">
                <form id="store_or_update_form" method="post">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="update_id">
                        <div class="form-group col-md-3 required">
                            <label for="start_date">Date</label>
                            <input type="text" class="form-control date" name="date" id="date" value="{{ date('Y-m-d') }}" readonly />
                        </div>
                        <div class="form-group col-md-3 required">
                            <label for="serial_no">Serial No</label>
                            <input type="text" class="form-control" name="serial_no" id="serial_no" value="{{ $serial_no }}" readonly />
                        </div>
                        <x-form.selectbox labelName="Finish Goods" name="product_id" required="required" onchange="formulationNumber(this.value)" col="col-md-3" class="selectpicker">
                            @if (!$products->isEmpty())
                                @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-unitid="{{ $product->unit_id }}" data-unitname="{{ $product->unit->unit_name }}">{{ $product->name.' - '.$product->code }}</option>
                                @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.textbox labelName="FG Serial No" name="formulation_no" required="required" col="col-md-3" property="readonly"/>
                        
                        <div class="form-group col-md-3 required">
                            <label for="unit_name">Unit</label>
                            <input type="text" class="form-control" name="unit_name" id="unit_name" readonly />
                            <input type="hidden" class="form-control" name="unit_id" id="unit_id" readonly />
                        </div>
                        <div class="form-group col-md-3 required">
                            <label for="total_fg_qty">Total FG Quantity</label>
                            <input type="text" class="form-control" name="total_fg_qty" id="total_fg_qty" />
                        </div>

                        <div class="col-md-12 pb-5">
                            <table class="table table-borderless pb-5" id="material_table">
                                <thead class="bg-primary">
                                    <th width="35%">Material</th>
                                    <th width="10%" class="text-center">Category</th>
                                    <th width="10%" class="text-center">Unit Name</th>
                                    <th width="10%" class="text-right">RM Rate</th>
                                    <th width="15%" class="text-center">RM Quantity</th>
                                    <th width="15%" class="text-right">RM Value</th>
                                    <th width="5%" class="text-center"> <i class="fas fa-trash text-white"></i> </th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select class="form-control selectpicker" name="materials[1][material_id]" id="materials_1_material_id" data-id="1" onchange="setRowValue(1)" data-live-search="true">
                                                <option value="">Select Please</option>
                                                @if (!$materials->isEmpty())
                                                    @foreach ($materials as $material)
                                                    <option value="{{ $material->id }}" data-category="{{ $material->category->name }}" data-unitid="{{ $material->unit_id }}" data-unitname="{{ $material->unit->unit_name }}" data-rate="{{ $material->cost }}">{{ $material->material_name.' - '.$material->material_code }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control text-center" name="materials[1][category]" id="materials_1_category" data-id="1" readonly>
                                        </td>
                                        <td>
                                            <input type="hidden" class="form-control" name="materials[1][unit_id]" id="materials_1_unit_id" data-id="1">
                                            <input type="text" class="form-control text-center" name="materials[1][unit_name]" id="materials_1_unit_name" data-id="1" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control text-right" name="materials[1][rate]" id="materials_1_rate" data-id="1" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control text-center qty" name="materials[1][qty]" id="materials_1_qty" onkeyup="calculateTotal(1)" data-id="1">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control text-right total" name="materials[1][total]" id="materials_1_total" data-id="1" readonly>
                                        </td>
                                        <td class="text-center"></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td><button type="button" class="btn btn-primary btn-sm" id="add_more_material"> <i class="fas fa-plus-square text-white"></i> </button></td>
                                        <td colspan="3" class="text-right font-weight-bolder">Total</td>
                                        <td><input type="text" class="form-control text-center font-weight-bolder" name="total_material_qty" id="total_material_qty" readonly /></td>
                                        <td><input type="text" class="form-control text-right font-weight-bolder" name="total_material_value" id="total_material_value" readonly /></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                                    
                        <div class="form-group col-md-12">
                            <label for="note">Note</label>
                            <textarea class="form-control" name="note" rows="5" id="note"></textarea>
                        </div>
                               
                    </div>
                    <div class="row pt-5">
                        <div class="form-group col-md-12 text-center px-0">
                            <button type="button" class="btn btn-danger btn-sm mr-3"><i class="fas fa-sync-alt"></i> Reset</button>
                            <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Submit</button>
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
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
$(document).ready(function () {

    $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});

    /** Start :: Material Add More Dynamic Field **/
    var count = 1;
    function add_more_material_field(row){
        html = `<tr>
                    <td>
                        <select class="form-control selectpicker" name="materials[`+row+`][material_id]" id="materials_`+row+`_material_id" data-id="`+row+`" onchange="setRowValue(`+row+`)" data-live-search="true">
                            <option value="">Select Please</option>
                            @if (!$materials->isEmpty())
                                @foreach ($materials as $material)
                                <option value="{{ $material->id }}" data-category="{{ $material->category->name }}" data-unitid="{{ $material->unit_id }}" data-unitname="{{ $material->unit->unit_name }}" data-rate="{{ $material->cost }}">{{ $material->material_name.' - '.$material->material_code }}</option>
                                @endforeach
                            @endif
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control text-center" name="materials[`+row+`][category]" id="materials_`+row+`_category" data-id="`+row+`" readonly>
                    </td>
                    <td>
                        <input type="hidden" class="form-control" name="materials[`+row+`][unit_id]" id="materials_`+row+`_unit_id" data-id="`+row+`">
                        <input type="text" class="form-control text-center" name="materials[`+row+`][unit_name]" id="materials_`+row+`_unit_name" data-id="`+row+`" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control text-right" name="materials[`+row+`][rate]" id="materials_`+row+`_rate" data-id="`+row+`" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control text-center qty" name="materials[`+row+`][qty]" id="materials_`+row+`_qty" onkeyup="calculateTotal(`+row+`)" data-id="`+row+`">
                    </td>
                    <td>
                        <input type="text" class="form-control text-right total" name="materials[`+row+`][total]" id="materials_`+row+`_total" data-id="`+row+`" readonly>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove_material"> <i class="fas fa-minus-square text-white"></i> </button>
                    </td>
                </tr> `;
        $('#material_table').append(html);
        $('.selectpicker').selectpicker('refresh');
    }

    $(document).on('click','#add_more_material',function(){
        count++;
        add_more_material_field(count);
    });
    $(document).on('click','.remove_material',function(){
        count--;
        $(this).closest('tr').remove();
        totalValue();
    });

    /** End :: Material Add More **/


});

function formulationNumber(product_id)
{
    $('#unit_id').val($('#product_id option:selected').data('unitid'));
    $('#unit_name').val($('#product_id option:selected').data('unitname'));
    $.ajax({
        url:"{{ route('formulation.generate.number') }}",
        data:{product_id:product_id,_token:_token},
        type:"POST",
        dataType:"JSON",
        success:function(data){
            data ? $('#formulation_no').val(data) : $('#formulation_no').val('');
        },
    });
}
function setRowValue(row){
    $('#materials_'+row+'_category').val($('#materials_'+row+'_material_id option:selected').data('category'));
    $('#materials_'+row+'_unit_id').val($('#materials_'+row+'_material_id option:selected').data('unitid'));
    $('#materials_'+row+'_unit_name').val($('#materials_'+row+'_material_id option:selected').data('unitname'));
    $('#materials_'+row+'_rate').val($('#materials_'+row+'_material_id option:selected').data('rate'));
}

function calculateTotal(row)
{
    var rate = $('#materials_'+row+'_rate').val();
    var qty = $('#materials_'+row+'_qty').val();
    var total  = 0;
    if(rate > 0 && qty > 0)
    {
        total = parseFloat(rate * qty).toFixed(4);
        $('#materials_'+row+'_total').val(total);
    }else{
        $('#materials_'+row+'_total').val('');
    }
    totalValue();
}

function totalValue()
{
    total_raw_qty = 0;
    total_raw_value = 0;
    $('.qty').each(function() {
        if($(this).val() == ''){
            total_raw_qty += 0;
        }else{
            console.log(parseFloat($(this).val()));
            total_raw_qty += parseFloat($(this).val());
            console.log(total_raw_qty);
        }
    });
    $('#total_material_qty').val(total_raw_qty);
    $('.total').each(function() {
        if($(this).val() == ''){
            total_raw_value += 0;
        }else{
            total_raw_value += parseFloat($(this).val());
        }
    });
    $('#total_material_value').val(total_raw_value.toFixed(4));
}

function store_data(){
    let form = document.getElementById('store_or_update_form');
    let formData = new FormData(form);
    let url = "{{route('formulation.store.or.update')}}";
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
                    window.location.replace("{{ route('formulation') }}");
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