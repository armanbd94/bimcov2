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
                    
                    <a href="{{ route('product.mixing') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
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
                        <div class="col-md-12">
                            <div class="card card-custom card-fit card-border">
                                <div class="card-body py-5">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <x-form.selectbox labelName="Finish Goods" name="product_id" required="required" onchange="generateBatchNo(this.value);formulationData(this.value);" col="col-md-12" class="selectpicker">
                                                @if (!$products->isEmpty())
                                                    @foreach ($products as $product)
                                                    <option value="{{ $product->id }}" data-unitid="{{ $product->unit_id }}" data-unitname="{{ $product->unit->unit_name }}">{{ $product->name.' - '.$product->code }}</option>
                                                    @endforeach
                                                @endif
                                            </x-form.selectbox>
                                            <div class="form-group col-md-12 required">
                                                <label for="batch_no">Batch No. <i class="fas fa-info-circle" data-toggle="tooltip" data-theme="dark" title="Batch No. Format : Product Code + Serial No of Production + Month + Year (101{{ date('my') }})"></i></label>
                                                <input type="text" class="form-control" name="batch_no" id="batch_no" readonly />
                                            </div>
                                            <div class="form-group col-md-12 required">
                                                <label for="date">Date</label>
                                                <input type="text" class="form-control date" name="date" id="date" value="{{ date('Y-m-d') }}" readonly />
                                            </div>
                                            <div class="form-group col-md-12 required">
                                                <label for="serial_no">Serial No</label>
                                                <input type="text" class="form-control" name="serial_no" id="serial_no" readonly />
                                            </div>
                                            <x-form.selectbox labelName="FG Serial No" name="formulation_no" required="required"  col="col-md-12" class="selectpicker"/>
                                            <div class="col-md-12">
                                                <div class="row" style="justify-content: space-evenly;">
                                                    <button type="button" class="btn btn-sm btn-success col-md-5" id="active-btn" onclick="showFormulationData('active')">Show Active FG SL</button>
                                                    <button type="button" class="btn btn-sm btn-secondary col-md-5" id="all-btn" onclick="showFormulationData('all')">Show All FG SL</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group col-md-12 required">
                                                <label for="unit_name">Unit</label>
                                                <input type="text" class="form-control" name="unit_name" id="unit_name" readonly />
                                                <input type="hidden" class="form-control" name="unit_id" id="unit_id" readonly />
                                            </div>
                                            <x-form.selectbox labelName="Total Year" name="year" required="required" onchange="generateDate(this.value);" col="col-md-12" class="selectpicker">
                                                @for ($i = 1; $i <= 3; $i++)
                                                <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
                                            </x-form.selectbox>
                                            <div class="form-group col-md-12 required">
                                                <label for="mfg_date">Mfg. Date</label>
                                                <input type="text" class="form-control date" name="mfg_date" id="mfg_date" value="{{ date('Y-m-d') }}" readonly />
                                            </div>
                                            <div class="form-group col-md-12 required">
                                                <label for="exp_date">Exp. Date</label>
                                                <input type="text" class="form-control date" name="exp_date" id="exp_date" value="{{ date('Y-m-d',strtotime('+1 year')) }}" readonly />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group col-md-12 required">
                                                <label for="total_fg_qty">Total FG Quantity</label>
                                                <input type="text" class="form-control" name="total_fg_qty" id="total_fg_qty" onkeyup="totalCostCalculation()" />
                                            </div>
                                            <x-form.selectbox labelName="Finish Goods Warehouse" name="pwarehouse_id" required="required" col="col-md-12" class="selectpicker">
                                                @if (!$warehouses->isEmpty())
                                                @foreach ($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                @endforeach 
                                                @endif
                                            </x-form.selectbox>
                                            <x-form.selectbox labelName="Material Warehouse" name="mwarehouse_id" required="required" col="col-md-12" class="selectpicker">
                                                @if (!$warehouses->isEmpty())
                                                @foreach ($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                @endforeach 
                                                @endif
                                            </x-form.selectbox>
                                            @if(permission('product-mixing-approve'))
                                            <x-form.selectbox labelName="Approve Mixing" name="status" required="required" col="col-md-12" class="selectpicker">
                                                @foreach (APPROVE_STATUS as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endforeach 
                                            </x-form.selectbox>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 py-5">
                            <table class="table table-borderless pb-5" id="material_table">
                                <thead class="bg-primary">
                                    <th width="30%">Material</th>
                                    <th class="text-center">Category</th>
                                    <th class="text-center">Unit Name</th>
                                    <th class="text-right">RM Rate</th>
                                    <th class="text-right">RM Quantity</th>
                                    <th class="text-right">RM Value</th>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>      
                        <div class="col-md-12 py-5">
                            <div class="col-md-12 text-center">
                                <h5 class="bg-warning text-white p-3" style="width:250px;margin: 20px auto 10px auto;">Materials Adjustment</h5>
                            </div>
                            <table class="table table-borderless pb-5" id="material_mixing_table">
                                <thead class="bg-primary">
                                    <th width="25%">Material</th>
                                    <th class="text-center">Category</th>
                                    <th class="text-center">Prefix</th>
                                    <th class="text-center">Unit Name</th>
                                    <th class="text-right">RM Rate</th>
                                    <th class="text-center">RM Quantity</th>
                                    <th class="text-right">RM Value</th>
                                    <th class="text-center"> <i class="fas fa-trash text-white"></i> </th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select class="form-control selectpicker" name="packaging_materials[1][material_id]" id="packaging_materials_1_material_id" data-id="1" onchange="setRowValue(1)" data-live-search="true">
                                                <option value="">Select Please</option>
                                                @if (!$materials->isEmpty())
                                                    @foreach ($materials as $material)
                                                    <option value="{{ $material->id }}" data-category="{{ $material->category->name }}" data-unitid="{{ $material->unit_id }}" data-unitname="{{ $material->unit->unit_name }}" data-rate="{{ $material->cost }}">{{ $material->material_name.' - '.$material->material_code }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control text-center" name="packaging_materials[1][category]" id="packaging_materials_1_category" data-id="1" readonly>
                                        </td>
                                        <td>
                                            <select class="form-control prefix" name="packaging_materials[1][prefix]" id="packaging_materials_1_prefix" onchange="calculateRowTotal(1)" data-id="1">
                                                <option value="+">+</option>
                                                <option value="-">-</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="hidden" class="form-control" name="packaging_materials[1][unit_id]" id="packaging_materials_1_unit_id" data-id="1">
                                            <input type="text" class="form-control text-center" name="packaging_materials[1][unit_name]" id="packaging_materials_1_unit_name" data-id="1" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control text-right" name="packaging_materials[1][rate]" id="packaging_materials_1_rate" data-id="1" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control text-right qty" name="packaging_materials[1][qty]" id="packaging_materials_1_qty" onkeyup="calculateRowTotal(1)" data-id="1">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control text-right total" name="packaging_materials[1][total]" id="packaging_materials_1_total" data-id="1" readonly>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove_material"> <i class="fas fa-minus-square text-white"></i> </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7"><button type="button" class="btn btn-primary btn-sm" id="add_more_material"> <i class="fas fa-plus-square text-white"></i> </button></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <table class="table">
                                <tr>
                                    <td class="text-right font-weight-bolder w-50">Total Cost</td>
                                    <td class="w-50"><input type="text" class="form-control text-right font-weight-bolder" name="total_cost" id="total_cost" readonly /></td>
                                </tr>
                                <tr>
                                    <td class="text-right font-weight-bolder w-50">Extra Cost</td>
                                    <td class="w-50"><input type="text" class="form-control text-right font-weight-bolder" onkeyup="totalCostCalculation()" name="extra_cost" id="extra_cost" /></td>
                                </tr>
                                <tr>
                                    <td class="text-right font-weight-bolder w-50">Total Net Cost</td>
                                    <td class="w-50"><input type="text" class="form-control text-right font-weight-bolder" name="total_net_cost" id="total_net_cost" readonly /></td>
                                </tr>
                                <tr>
                                    <td class="text-right font-weight-bolder w-50">Per Unit Cost</td>
                                    <td class="w-50"><input type="text" class="form-control text-right font-weight-bolder" name="per_unit_cost" id="per_unit_cost" readonly /></td>
                                </tr>
                            </table>
                        </div>   
                        <div class="col-md-12 text-right pt-5">
                            <button type="button" class="btn btn-primary btn-sm" onclick="check_material_stock()" id="save-btn"><i class="fas fa-save"></i> Save</button>
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

var count = 1;

$(document).ready(function () {


    $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});

    /** Start :: Material Add More Dynamic Field **/
    
    function add_more_material_field(row){
        html = `<tr>
                    <td>
                        <select class="form-control selectpicker" name="packaging_materials[`+row+`][material_id]" id="packaging_materials_`+row+`_material_id" data-id="`+row+`" onchange="setRowValue(`+row+`)" data-live-search="true">
                            <option value="">Select Please</option>
                            @if (!$materials->isEmpty())
                                @foreach ($materials as $material)
                                <option value="{{ $material->id }}" data-category="{{ $material->category->name }}" data-unitid="{{ $material->unit_id }}" data-unitname="{{ $material->unit->unit_name }}" data-rate="{{ $material->cost }}">{{ $material->material_name.' - '.$material->material_code }}</option>
                                @endforeach
                            @endif
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control text-center" name="packaging_materials[`+row+`][category]" id="packaging_materials_`+row+`_category" data-id="`+row+`" readonly>
                    </td>
                    <td>
                        <select class="form-control prefix" name="packaging_materials[`+row+`][prefix]" id="packaging_materials_`+row+`_prefix" onchange="calculateRowTotal(`+row+`)" data-id="`+row+`">
                            <option value="+">+</option>
                            <option value="-">-</option>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" class="form-control" name="packaging_materials[`+row+`][unit_id]" id="packaging_materials_`+row+`_unit_id" data-id="`+row+`">
                        <input type="text" class="form-control text-center" name="packaging_materials[`+row+`][unit_name]" id="packaging_materials_`+row+`_unit_name" data-id="`+row+`" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control text-right" name="packaging_materials[`+row+`][rate]" id="packaging_materials_`+row+`_rate" data-id="`+row+`" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control text-right qty" name="packaging_materials[`+row+`][qty]" id="packaging_materials_`+row+`_qty" onkeyup="calculateRowTotal(`+row+`)" data-id="`+row+`">
                    </td>
                    <td>
                        <input type="text" class="form-control text-right total" name="packaging_materials[`+row+`][total]" id="packaging_materials_`+row+`_total" data-id="`+row+`" readonly>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove_material"> <i class="fas fa-minus-square text-white"></i> </button>
                    </td>
                </tr> `;
        $('#material_mixing_table').append(html);
        $('.selectpicker').selectpicker('refresh');
    }

    $(document).on('click','#add_more_material',function(){
        count++;
        add_more_material_field(count);
    });
    $(document).on('click','.remove_material',function(){
        count--;
        $(this).closest('tr').remove();
        totalCostCalculation();
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
            setFrmulationData();
            formulationMaterials();
        },
    });
}

function generateBatchNo(product_id)
{
    $.ajax({
        url:"{{ url('generate-batch-no') }}/"+product_id,
        type:"GET",
        success:function(data){
            $('#batch_no').val(data);
            $('.selectpicker').selectpicker('refresh');
        },
    });
}

function setFrmulationData(){
    $('#unit_id').val($('#formulation_no option:selected').data('unitid'));
    $('#unit_name').val($('#formulation_no option:selected').data('unitname'));
    $('#total_fg_qty').val($('#formulation_no option:selected').data('totalfgqty'));
    $('#serial_no').val($('#formulation_no option:selected').val());
}
function formulationMaterials()
{
    var formulation_id = $('#formulation_no option:selected').val();
    $.ajax({
        url:"{{ url('formulation-materials') }}",
        data:{formulation_id:formulation_id,_token:_token},
        type:"POST",
        success:function(data){
            $('#material_table tbody').html('');
            $('#material_table tbody').html(data);
            $('.selectpicker').selectpicker('refresh');
            totalCostCalculation();
            count = $('#material_table tbody tr').length;
            count > 0 ? $('.check-section').removeClass('d-none') : $('.check-section').addClass('d-none');
        },
    });
}
function setRowValue(row){
    $('#packaging_materials_'+row+'_category').val($('#packaging_materials_'+row+'_material_id option:selected').data('category'));
    $('#packaging_materials_'+row+'_unit_id').val($('#packaging_materials_'+row+'_material_id option:selected').data('unitid'));
    $('#packaging_materials_'+row+'_unit_name').val($('#packaging_materials_'+row+'_material_id option:selected').data('unitname'));
    $('#packaging_materials_'+row+'_rate').val($('#packaging_materials_'+row+'_material_id option:selected').data('rate'));
}

function calculateRowTotal(row)
{
    var rate = $('#packaging_materials_'+row+'_rate').val();
    var qty = $('#packaging_materials_'+row+'_qty').val();
    var total  = 0;
    if(rate > 0 && qty > 0)
    {
        total = parseFloat(rate * qty).toFixed(4);
        $('#packaging_materials_'+row+'_total').val(total);
    }else{
        $('#packaging_materials_'+row+'_total').val('');
    }
    totalCostCalculation();
}

function totalCostCalculation()
{
    // total_raw_qty = 0;
    // total_raw_value = 0;
    // $('.qty').each(function() {
    //     if($(this).val() == ''){
    //         total_raw_qty += 0;
    //     }else{
    //         total_raw_qty += parseFloat($(this).val());
    //     }
    // });
    // $('#total_material_qty').val(total_raw_qty);
    // $('.total').each(function() {
    //     if($(this).val() == ''){
    //         total_raw_value += 0;
    //     }else{
    //         total_raw_value += parseFloat($(this).val());
    //     }
    // });
    // $('#total_material_value').val(total_raw_value.toFixed(4));
    var total_cost = 0;
    var total_fg_qty = 0;
    if($('#total_fg_qty').val()){
        total_fg_qty = $('#total_fg_qty').val();
    }else{
        $('#total_fg_qty').val(1);
        total_fg_qty = 1;
    }
    totalMaterialCalculation(total_fg_qty);
    $('.total').each(function() {
        if($(this).val() == ''){
            total_cost += 0;
        }else{
            var row = $(this).data('id');
            var prefix = $(this).closest("tr").find(".prefix").val();
            if(prefix == '-')
            {
                total_cost -= parseFloat($(this).val());
            }else{
                total_cost += parseFloat($(this).val());
            }
            
        }
    });
    
    totalNetCalculation(total_fg_qty,total_cost);
}

function totalNetCalculation(total_fg_qty,total_cost)
{
    $('#total_cost').val(total_cost.toFixed(4));
    var extra_cost = 0;
    if($('#extra_cost').val())
    {
        extra_cost = parseFloat($('#extra_cost').val());
    }
    $('#total_net_cost').val(parseFloat(total_cost + extra_cost).toFixed(4));
    var per_unit_cost = total_fg_qty > 0 ? parseFloat((total_cost + extra_cost) / total_fg_qty) : 0;
    $('#per_unit_cost').val(parseFloat(per_unit_cost).toFixed(4));
    
}
function totalMaterialCalculation(total_fg_qty)
{
    if(total_fg_qty){
        $('#material_table .per_unit_total').each(function() {
            if($(this).val()){
                var trow = $(this).data('id');
                var ttotal = total_fg_qty * $(this).val();
                $('#materials_'+trow+'_total').val(parseFloat(ttotal).toFixed(4));
            }
        });
        $('#material_table .per_unit_qty').each(function() {
            if($(this).val()){
                var prow = $(this).data('id');
                var ptotal = total_fg_qty * $(this).val();
                $('#materials_'+prow+'_qty').val(parseFloat(ptotal));
            }
        });
    }
    
}
function showFormulationData(type)
{
    var formulation_no = $('#formulation_no option:selected').val();
    var product_id = $('#product_id option:selected').val();
    if(product_id)
    {
        $.ajax({
            url:"{{ url('product-mixing-formulation-data') }}",
            data:{product_id:product_id,formulation_id:formulation_no,type:type,_token:_token},
            type:"POST",
            beforeSend: function(){
                $('#'+type+'-btn').addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#'+type+'-btn').removeClass('spinner spinner-white spinner-right');
            },
            success: function (data) {
                $('#view_modal #view-data').html('');
                $('#view_modal #view-data').html(data);
                $('#view_modal').modal({
                    keyboard: false,
                    backdrop: 'static',
                });
                $('#view_modal .modal-title').html('<i class="fas fa-file-alt text-white"></i> <span> Finish Goods Formulation Details</span>');
            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }else{
        notification('error','Please select finish goods');
    }
    
}

function generateDate(number)
{
//     var year  = new Date('2021-06-20').getFullYear();
//    var month = new Date('2021-06-20').getMonth();
//    var day   = new Date('2021-06-20').getDate();
//    var date  = new Date(year + 1, month, day);
//    console.log(date);
//     const nr_years = 3;

    var mfg_date = $('#mfg_date').val();
    var exp_date = new Date(new Date(mfg_date).setFullYear(new Date(mfg_date).getFullYear() + parseInt(number)));
    $('#exp_date').val(exp_date.toISOString().slice(0, 10));
}

function check_material_stock()
{
    var rownumber = $('#material_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert material to table!")
    }else{
        if($('#mwarehouse_id').val()){
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
                        store_data();
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
        }else{
            notification("error","Please select material warehouse");
        }
       
    }
}

function store_data(){

    let form = document.getElementById('store_or_update_form');
    let formData = new FormData(form);
    let url = "{{url('product-mixing/store')}}";
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