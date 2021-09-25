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
                    <a href="{{ route('production.adjustment') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
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
                    <input type="hidden" name="update_id" value="{{ $production->id }}">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-custom card-fit card-border">
                                <div class="card-body py-5">
                                    <div class="row">
                                        <div class="form-group col-md-4 required">
                                            <label for="batch_no">Batch No.</label>
                                            <input type="text" class="form-control" name="batch_no" id="batch_no" value="{{ $production->batch_no }}" readonly />
                                        </div>
                                        <div class="form-group col-md-4 required">
                                            <label for="mfg_date">Mfg. Date</label>
                                            <input type="text" class="form-control date" name="mfg_date" id="mfg_date" value="{{ $production->mfg_date }}" readonly />
                                        </div>
                                        <div class="form-group col-md-4 required">
                                            <label for="exp_date">Exp. Date</label>
                                            <input type="text" class="form-control date" name="exp_date" id="exp_date" value="{{ $production->exp_date }}" readonly />
                                        </div>
                                        <div class="form-group col-md-4 required">
                                            <label for="date">Date</label>
                                            <input type="text" class="form-control date" name="date" id="date" value="{{ $production->date }}" readonly />
                                        </div>
                                
                                        <x-form.selectbox labelName="Finish Goods" name="product_id" required="required" onchange="formulationData(this.value)" col="col-md-4" class="selectpicker">
                                            @if (!$products->isEmpty())
                                                @foreach ($products as $product)
                                                <option value="{{ $product->id }}"  {{ $production->product_id == $product->id ? 'selected' : '' }} data-unitid="{{ $product->unit_id }}" data-unitname="{{ $product->unit->unit_name }}">{{ $product->name.' - '.$product->code }}</option>
                                                @endforeach
                                            @endif
                                        </x-form.selectbox>
                                        <x-form.selectbox labelName="FG Serial No" name="formulation_no" required="required" onchange="setFrmulationData();formulationMaterials(this.value);" col="col-md-4" class="selectpicker">
                                            @if (!$formulations->isEmpty())
                                                @foreach ($formulations as $item)
                                                    <option value="{{ $item->id }}" {{ $production->formulation_id == $item->id ? 'selected' : '' }}  data-unitid="{{ $item->unit_id }}" data-unitname="{{ $item->unit->unit_name }}" data-totalfgqty="{{ $item->total_fg_qty }}">{{ $item->formulation_no }}</option>
                                                @endforeach
                                            @endif
                                        </x-form.selectbox>
                                        <div class="form-group col-md-4 required">
                                            <label for="serial_no">Serial No</label>
                                            <input type="text" class="form-control" name="serial_no" id="serial_no" value=" {{ $production->formulation_id }}" readonly />
                                        </div>
                                        <div class="form-group col-md-4 required">
                                            <label for="unit_name">Unit</label>
                                            <input type="text" class="form-control" name="unit_name" id="unit_name" value="{{ $production->unit->unit_name }}" readonly />
                                            <input type="hidden" class="form-control" name="unit_id" id="unit_id" value="{{ $production->unit_id }}" readonly />
                                        </div>
                                        <div class="form-group col-md-4 required">
                                            <label for="total_fg_qty">Total FG Quantity</label>
                                            <input type="text" class="form-control" name="total_fg_qty" id="total_fg_qty" value=" {{ $production->total_fg_qty }}" onkeyup="totalNetCalculation()" />
                                        </div>
                                        <x-form.selectbox labelName="Product Warehouse" name="pwarehouse_id" required="required" col="col-md-4" class="selectpicker">
                                            @if (!$warehouses->isEmpty())
                                            @foreach ($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}" {{ $production->pwarehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                            @endforeach 
                                            @endif
                                        </x-form.selectbox>
                                        <x-form.selectbox labelName="Material Warehouse" name="mwarehouse_id" required="required" col="col-md-4" class="selectpicker">
                                            @if (!$warehouses->isEmpty())
                                            @foreach ($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}" {{ $production->mwarehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                            @endforeach 
                                            @endif
                                        </x-form.selectbox>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 py-5">
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
                                    @if (!$production->materials->isEmpty())
                                        @foreach ($production->materials as $key => $item)
                                        <tr>
                                            <td>
                                                <select class="form-control selectpicker" name="materials[{{ $key+1 }}][material_id]" id="materials_{{ $key+1 }}_material_id" data-id="{{ $key+1 }}" onchange="setRowValue('{{ $key+1 }}')" data-live-search="true">
                                                    <option value="">Select Please</option>
                                                    @if (!$materials->isEmpty())
                                                        @foreach ($materials as $material)
                                                        <option value="{{ $material->id }}" {{ $item->id == $material->id ? 'selected' : '' }} data-category="{{ $material->category->name }}" data-unitid="{{ $material->unit_id }}" data-unitname="{{ $material->unit->unit_name }}" data-rate="{{ $material->cost }}">{{ $material->material_name.' - '.$material->material_code }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control text-center" value="{{ $item->category->name }}" name="materials[{{ $key+1 }}][category]" id="materials_{{ $key+1 }}_category" data-id="{{ $key+1 }}" readonly>
                                            </td>
                                            <td>
                                                <input type="hidden" class="form-control" value="{{ $item->unit_id }}" name="materials[{{ $key+1 }}][unit_id]" id="materials_{{ $key+1 }}_unit_id" data-id="{{ $key+1 }}">
                                                <input type="text" class="form-control text-center" value="{{ $item->unit->unit_name }}" name="materials[{{ $key+1 }}][unit_name]" id="materials_{{ $key+1 }}_unit_name" data-id="{{ $key+1 }}" readonly>
                                            </td>
                                            <td> 
                                                <input type="text" class="form-control text-right" value="{{ $item->pivot->rate }}" name="materials[{{ $key+1 }}][rate]" id="materials_{{ $key+1 }}_rate" data-id="{{ $key+1 }}" readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control text-center qty" value="{{ $item->pivot->qty }}" name="materials[{{ $key+1 }}][qty]" id="materials_{{ $key+1 }}_qty" onkeyup="calculateTotal('{{ $key+1 }}')" data-id="{{ $key+1 }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control text-right total"  value="{{ $item->pivot->total }}" name="materials[{{ $key+1 }}][total]" id="materials_{{ $key+1 }}_total" data-id="{{ $key+1 }}" readonly>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm remove_material"> <i class="fas fa-minus-square text-white"></i> </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
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
                                    @endif
                                    
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td><button type="button" class="btn btn-primary btn-sm" id="add_more_material"> <i class="fas fa-plus-square text-white"></i> </button></td>
                                        <td colspan="4" class="text-right font-weight-bolder">Product Per Unit RM Qty</td>
                                        <td colspan="2"><input type="text" class="form-control text-right font-weight-bolder" value=" {{ $production->per_unit_rm_qty }}" name="total_material_qty" id="total_material_qty" readonly /></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right font-weight-bolder">Product Per Unit RM Cost</td>
                                        <td colspan="2"><input type="text" class="form-control text-right font-weight-bolder" value=" {{ number_format($production->per_unit_rm_value,4,'.','') }}" name="total_material_value" id="total_material_value" readonly /></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right font-weight-bolder">Total RM Qty</td>
                                        <td colspan="2"><input type="text" class="form-control text-right font-weight-bolder" value=" {{ $production->total_rm_qty }}" name="total_rm_qty" id="total_rm_qty" readonly /></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right font-weight-bolder">Total Cost</td>
                                        <td colspan="2"><input type="text" class="form-control text-right font-weight-bolder" value=" {{ number_format($production->total_cost,4,'.','') }}" name="total_cost" id="total_cost" readonly /></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right font-weight-bolder">Extra Cost</td>
                                        <td colspan="2"><input type="text" class="form-control text-right font-weight-bolder" value=" {{ number_format($production->extra_cost,4,'.','')}}" onkeyup="totalNetCalculation()" name="extra_cost" id="extra_cost" /></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right font-weight-bolder">Total Net Cost</td>
                                        <td colspan="2"><input type="text" class="form-control text-right font-weight-bolder" value=" {{ number_format($production->total_net_cost,4,'.','')}}" name="total_net_cost" id="total_net_cost" readonly /></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right font-weight-bolder">Per Unit Cost</td>
                                        <td colspan="2"><input type="text" class="form-control text-right font-weight-bolder" name="per_unit_cost" id="per_unit_cost" readonly /></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="col-md-12 text-right pt-5">
                                <button type="button" class="btn btn-primary btn-sm" onclick="check_material_stock()" id="save-btn"><i class="fas fa-save"></i> Save</button>
                            </div>
                        </div>      
                    </div>
                </form>
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@include('production::production.material-stock-modal')
@endsection

@push('scripts')
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
@if (isset($production))
var count = "{{ count($production['materials']) }}";
@else
var count = 1;
@endif
                                        

$(document).ready(function () {

    $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});

    /** Start :: Material Add More Dynamic Field **/
    
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

function formulationData(product_id, formulation_no='')
{
    $.ajax({
        url:"{{ url('formulation-data') }}",
        data:{product_id:product_id,_token:_token},
        type:"POST",
        success:function(data){
            $('#formulation_no').html('');
            $('#formulation_no').html(data);
            $('.selectpicker').selectpicker('refresh');
            if(formulation_no){
                $('#formulation_no').val(formulation_no);
                $('.selectpicker').selectpicker('refresh');
            }
        },
    });
}


function setFrmulationData(){
    $('#unit_id').val($('#formulation_no option:selected').data('unitid'));
    $('#unit_name').val($('#formulation_no option:selected').data('unitname'));
    $('#total_fg_qty').val($('#formulation_no option:selected').data('totalfgqty'));
    $('#serial_no').val($('#formulation_no option:selected').val());
}
function formulationMaterials(formulation_id)
{
    $.ajax({
        url:"{{ url('formulation-materials') }}",
        data:{formulation_id:formulation_id,_token:_token},
        type:"POST",
        success:function(data){
            $('#material_table tbody').html('');
            $('#material_table tbody').html(data);
            $('.selectpicker').selectpicker('refresh');
            totalValue();
            count = $('#material_table tbody tr').length;
            count > 0 ? $('.check-section').removeClass('d-none') : $('.check-section').addClass('d-none');
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
            total_raw_qty += parseFloat($(this).val());
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
    totalNetCalculation();
}

function totalNetCalculation()
{
    var total_fg_qty = 0;
    if($('#total_fg_qty').val()){
        total_fg_qty = $('#total_fg_qty').val();
    }
    var per_unit_rm_qty  = $('#total_material_qty').val() ? parseFloat($('#total_material_qty').val()) : 0;
    var per_unit_rm_cost  = $('#total_material_value').val() ? parseFloat($('#total_material_value').val()) : 0;
    var total_rm_qty = parseFloat(total_fg_qty * per_unit_rm_qty);
    var total_cost = parseFloat(total_fg_qty * per_unit_rm_cost);
    $('#total_rm_qty').val(total_rm_qty);
    $('#total_cost').val(total_cost.toFixed(4));
    var extra_cost = $('#extra_cost').val() ? parseFloat($('#extra_cost').val()) : 0;
    $('#total_net_cost').val(parseFloat(total_cost + extra_cost).toFixed(4));
    var per_unit_cost = total_fg_qty > 0 ? parseFloat((total_cost + extra_cost) / total_fg_qty) : 0;
    $('#per_unit_cost').val(parseFloat(per_unit_cost).toFixed(4));
}

function check_material_stock()
{
    var rownumber = $('table#material_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert material to table!")
    }else{
        let form = document.getElementById('store_or_update_form');
        let formData = new FormData(form);
        let url = "{{url('production-check-material-stock')}}";
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
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
                if (data.status == 'success') {
                    update_data();
                }else{
                    $('#view_modal #view-data').html('');
                    $('#view_modal #view-data').html(data);
                    $('#view_modal').modal({
                        keyboard: false,
                        backdrop: 'static',
                    });
                    $('#view_modal .modal-title').html('<i class="fas fa-file-alt text-white"></i> <span> Material Stock Availibility Details</span>');
                }
            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }
}

function update_data(){
    let form = document.getElementById('store_or_update_form');
    let formData = new FormData(form);
    let url = "{{url('production-adjustment/update')}}";
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
                    window.location.replace("{{ route('product.mixing') }}");
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