@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<style>
    #form-tab li a.active{
        background: #034d97 !important;
        color: white !important;
    }
    .nav-link{
        position: relative;
        border-radius: 5px !important;
        background: #E4E6EF;
        color: #7E8299;
    }
    .remove-tab{
        position: absolute;
        top: -8px;
        right: -10px;
        border-radius: 50%;
    }
</style>
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
                    <button type="button" class="btn btn-primary btn-sm mr-5" onclick="store_data()" id="save-btn"><i class="fas fa-save"></i> Save</button>
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
                <div class="col-md-12">
                    <ul class="nav nav-tabs nav-tabs-2" id="form-tab" role="tablist" style="border-bottom: 0px !important;">
                        <li class="nav-item mx-0">
                            <a class="nav-link active text-center step  step-1" data-toggle="tab" href="#tab-1" role="tab">Tab-1</a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link text-center ml-5" id="add-new-tab" style="cursor: pointer;"><i class="fas fa-plus-circle mr-2"></i> Add New</a>
                        </li>
                    </ul>
                    <form id="store_or_update_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="tab" id="check_tab">
                        <div class="tab-content">
                            <div class="tab-pane active step step-1 p-3" id="tab-1" role="tabpanel">
                                @include('production::pre-production-calculation.form')
                            </div>
                            
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@include('production::pre-production-calculation.view-modal')
@endsection

@push('scripts')
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
$(document).ready(function () {
    $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});
    var tabcount = 1;
    function add_new_tab(tab){

        tab_btn_html = `<li class="nav-item mx-5" id="tab`+tab+`">
                            <a class="nav-link text-center step  step-`+tab+`" data-toggle="tab" href="#tab-`+tab+`" role="tab">Tab-`+tab+` <i data-tab="`+tab+`" class="fas fa-times-circle text-danger remove-tab ml-5 bg-white"></i></a>
                        </li>`;
        $('#form-tab li:last').before(tab_btn_html);

        tab_content_html = `<div class="tab-pane  step step-`+tab+`  p-3" id="tab-`+tab+`" role="tabpanel">
                                <div class="row"  id="production_`+tab+`">
                                    <div class="col-md-12">
                                        <div class="card card-custom card-fit card-border">
                                            <div class="card-body py-5">
                                                <div class="row">
                                                        <div class="form-group col-md-4 required">
                                                            <label >Finish Goods</label>
                                                            <select name="production[`+tab+`][product_id]" id="production_`+tab+`_product_id"  onchange="formulationData(this.value,`+tab+`)" class="form-control selectpicker">
                                                                <option value="">Select Please</option>
                                                                @if (!$products->isEmpty())
                                                                    @foreach ($products as $product)
                                                                    <option value="{{ $product->id }}" data-unitid="{{ $product->unit_id }}" data-unitname="{{ $product->unit->unit_name }}">{{ $product->name.' - '.$product->code }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-4 required">
                                                            <label >FG Serial No</label>
                                                            <select name="production[`+tab+`][formulation_no]" id="production_`+tab+`_formulation_no" class="form-control selectpicker">
                                                                <option value="">Select Please</option>
                                                                
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="row" style="justify-content: space-evenly;padding-top: 28px;">
                                                                <button type="button" class="btn btn-sm btn-success col-md-5" id="active-btn" onclick="showFormulationData('active',`+tab+`)">Show Active FG SL</button>
                                                                <button type="button" class="btn btn-sm btn-secondary col-md-5" id="all-btn" onclick="showFormulationData('all',`+tab+`)">Show All FG SL</button>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-4 required">
                                                            <label for="serial_no">Serial No</label>
                                                            <input type="text" class="form-control" name="production[`+tab+`][serial_no]" id="production_`+tab+`_serial_no" readonly />
                                                        </div>
                                                        <div class="form-group col-md-4 required">
                                                            <label for="date">Date</label>
                                                            <input type="text" class="form-control date" name="production[`+tab+`][date]" id="production_`+tab+`_date" value="{{ date('Y-m-d') }}" readonly />
                                                        </div>
                                                        <div class="form-group col-md-4 required">
                                                            <label for="unit_name">Unit</label>
                                                            <input type="text" class="form-control" name="production[`+tab+`][unit_name]" id="production_`+tab+`_unit_name" readonly />
                                                            <input type="hidden" class="form-control" name="production[`+tab+`][unit_id]" id="production_`+tab+`_unit_id" readonly />
                                                        </div>
                                                        <div class="form-group col-md-4 required">
                                                            <label for="total_fg_qty">Total FG Quantity</label>
                                                            <input type="text" class="form-control" name="production[`+tab+`][total_fg_qty]" id="production_`+tab+`_total_fg_qty" onkeyup="totalCostCalculation(`+tab+`)" />
                                                        </div>
                                                        <div class="form-group col-md-4 required">
                                                            <label >Material Warehouse</label>
                                                            <select name="production[`+tab+`][mwarehouse_id]" id="production_`+tab+`_mwarehouse_id" class="form-control selectpicker">
                                                                <option value="">Select Please</option>
                                                                @if (!$warehouses->isEmpty())
                                                                @foreach ($warehouses as $warehouse)
                                                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                                @endforeach 
                                                                @endif
                                                            </select>
                                                        </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 py-5">
                                        <table class="table table-borderless pb-5" id="material_table_`+tab+`">
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
                                        <table class="table table-borderless pb-5" id="material_mixing_table_`+tab+`">
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
                                                        <select class="form-control selectpicker" name="production[`+tab+`][packaging_materials][1][material_id]" id="production_`+tab+`_packaging_materials_1_material_id" data-id="1" onchange="setRowValue(`+tab+`,1)" data-live-search="true">
                                                            <option value="">Select Please</option>
                                                            @if (!$materials->isEmpty())
                                                                @foreach ($materials as $material)
                                                                <option value="{{ $material->id }}" data-category="{{ $material->category->name }}" data-unitid="{{ $material->unit_id }}" data-unitname="{{ $material->unit->unit_name }}" data-rate="{{ $material->cost }}">{{ $material->material_name.' - '.$material->material_code }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control text-center" name="production[`+tab+`][packaging_materials][1][category]" id="production_`+tab+`_packaging_materials_1_category" data-id="1" readonly>
                                                    </td>
                                                    <td>
                                                        <select class="form-control prefix" name="production[`+tab+`][packaging_materials][1][prefix]" id="production_`+tab+`_packaging_materials_1_prefix" onchange="calculateRowTotal(`+tab+`,1)" data-id="1">
                                                            <option value="+">+</option>
                                                            <option value="-">-</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" class="form-control" name="production[`+tab+`][packaging_materials][1][unit_id]" id="production_`+tab+`_packaging_materials_1_unit_id" data-id="1">
                                                        <input type="text" class="form-control text-center" name="production[`+tab+`][packaging_materials][1][unit_name]" id="production_`+tab+`_packaging_materials_1_unit_name" data-id="1" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control text-right" name="production[`+tab+`][packaging_materials][1][rate]" id="production_`+tab+`_packaging_materials_1_rate" data-id="1" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control text-right qty" name="production[`+tab+`][packaging_materials][1][qty]" id="production_`+tab+`_packaging_materials_1_qty" onkeyup="calculateRowTotal(`+tab+`,1)" data-id="1">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control text-right total" name="production[`+tab+`][packaging_materials][1][total]" id="production_`+tab+`_packaging_materials_1_total" data-id="1" readonly>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-danger btn-sm remove_material" data-tab="`+tab+`"> <i class="fas fa-minus-square text-white"></i> </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="7"><button type="button" class="btn btn-primary btn-sm add_more_material" id="add_more_material" data-tab="`+tab+`"> <i class="fas fa-plus-square text-white"></i> </button></td>
                                                </tr>
                                            </tfoot>
                                        </table>

                                        <table class="table">
                                            <tr>
                                                <td class="text-right font-weight-bolder w-50">Total Cost</td>
                                                <td class="w-50"><input type="text" class="form-control text-right font-weight-bolder" name="production[`+tab+`][total_cost]" id="production_`+tab+`_total_cost" readonly /></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right font-weight-bolder w-50">Extra Cost</td>
                                                <td class="w-50"><input type="text" class="form-control text-right font-weight-bolder" onkeyup="totalCostCalculation(`+tab+`)" name="production[`+tab+`][extra_cost]" id="production_`+tab+`_extra_cost" /></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right font-weight-bolder w-50">Total Net Cost</td>
                                                <td class="w-50"><input type="text" class="form-control text-right font-weight-bolder" name="production[`+tab+`][total_net_cost]" id="production_`+tab+`_total_net_cost" readonly /></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right font-weight-bolder w-50">Per Unit Cost</td>
                                                <td class="w-50"><input type="text" class="form-control text-right font-weight-bolder" name="production[`+tab+`][per_unit_cost]" id="production_`+tab+`_per_unit_cost" readonly /></td>
                                            </tr>
                                        </table>
                                    </div>   
                                    <div class="col-md-12 text-right pt-5">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="check_material_stock(`+tab+`)" id="check-btn-`+tab+`"> Check</button>
                                    </div>   
                                </div>
                            </div>`
        $('.tab-content').append(tab_content_html);
        $('.selectpicker').selectpicker('refresh');
    }

    $(document).on('click','#add-new-tab',function(){
        tabcount++;
        add_new_tab(tabcount);
    });
    $(document).on('click','.remove-tab',function(){
        var tab = $(this).data('tab');
        Swal.fire({
            title: 'Are you sure to delete Tab-' + tab + '?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.value) {
                
                if($('#form-tab li#tab'+tab).is(':nth-last-child(2)'))
                {
                    tabcount--;
                }
                $('#tab'+tab).remove();
                Swal.fire("Removed", "Tab Removed Successfully", "success");
            }
        });
    });

    var count = 1;
    function add_more_material_field(tab,row){
        html = `<tr>
                    <td>
                        <select class="form-control selectpicker" name="production[`+tab+`][packaging_materials][`+row+`][material_id]" id="production_`+tab+`_packaging_materials_`+row+`_material_id" data-id="`+row+`" onchange="setRowValue(`+tab+`,`+row+`)" data-live-search="true">
                            <option value="">Select Please</option>
                            @if (!$materials->isEmpty())
                                @foreach ($materials as $material)
                                <option value="{{ $material->id }}" data-category="{{ $material->category->name }}" data-unitid="{{ $material->unit_id }}" data-unitname="{{ $material->unit->unit_name }}" data-rate="{{ $material->cost }}">{{ $material->material_name.' - '.$material->material_code }}</option>
                                @endforeach
                            @endif
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control text-center" name="production[`+tab+`][packaging_materials][`+row+`][category]" id="production_`+tab+`_packaging_materials_`+row+`_category" data-id="`+row+`" readonly>
                    </td>
                    <td>
                        <select class="form-control prefix" name="production[`+tab+`][packaging_materials][`+row+`][prefix]" id="production_`+tab+`_packaging_materials_`+row+`_prefix" onchange="calculateRowTotal(`+tab+`,`+row+`)" data-id="`+row+`">
                            <option value="+">+</option>
                            <option value="-">-</option>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" class="form-control" name="production[`+tab+`][packaging_materials][`+row+`][unit_id]" id="production_`+tab+`_packaging_materials_`+row+`_unit_id" data-id="`+row+`">
                        <input type="text" class="form-control text-center" name="production[`+tab+`][packaging_materials][`+row+`][unit_name]" id="production_`+tab+`_packaging_materials_`+row+`_unit_name" data-id="`+row+`" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control text-right" name="production[`+tab+`][packaging_materials][`+row+`][rate]" id="production_`+tab+`_packaging_materials_`+row+`_rate" data-id="`+row+`" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control text-right qty" name="production[`+tab+`][packaging_materials][`+row+`][qty]" id="production_`+tab+`_packaging_materials_`+row+`_qty" onkeyup="calculateRowTotal(`+tab+`,`+row+`)" data-id="`+row+`">
                    </td>
                    <td>
                        <input type="text" class="form-control text-right total" name="production[`+tab+`][packaging_materials][`+row+`][total]" id="production_`+tab+`_packaging_materials_`+row+`_total" data-id="`+row+`" readonly>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove_material" data-tab="`+tab+`"> <i class="fas fa-minus-square text-white"></i> </button>
                    </td>
                </tr> `;
        $('#material_mixing_table_'+tab).append(html);
        $('.selectpicker').selectpicker('refresh');
    }

    $(document).on('click','.add_more_material',function(){
        var tab_no = $(this).data('tab');
        count++;
        add_more_material_field(tab_no,count);
    });
    $(document).on('click','.remove_material',function(){
        var tab_no  =  $(this).data('tab');
        count--;
        $(this).closest('tr').remove();
        totalCostCalculation(tab_no);
    });
});


function formulationData(product_id, tab)
{
    $.ajax({
        url:"{{ url('formulation-data') }}",
        data:{product_id:product_id,_token:_token},
        type:"POST",
        success:function(data){
            $('#production_'+tab+'_formulation_no').html('');
            $('#production_'+tab+'_formulation_no').html(data);
            $('.selectpicker').selectpicker('refresh');
            setFormulationData(tab);
            formulationMaterials(tab);
        },
    });
}

function setFormulationData(tab){
    $('#production_'+tab+'_unit_id').val($('#production_'+tab+'_formulation_no option:selected').data('unitid'));
    $('#production_'+tab+'_unit_name').val($('#production_'+tab+'_formulation_no option:selected').data('unitname'));
    $('#production_'+tab+'_total_fg_qty').val($('#production_'+tab+'_formulation_no option:selected').data('totalfgqty'));
    $('#production_'+tab+'_serial_no').val($('#production_'+tab+'_formulation_no option:selected').val());
}
function formulationMaterials(tab)
{
    var formulation_id = $('#production_'+tab+'_formulation_no option:selected').val();
    $.ajax({
        url:"{{ url('pre-production-formulation-materials') }}",
        data:{formulation_id:formulation_id,tab:tab,_token:_token},
        type:"POST",
        success:function(data){
            $('#production_'+tab+' #material_table_'+tab+' tbody').html('');
            $('#production_'+tab+' #material_table_'+tab+' tbody').html(data);
            $('.selectpicker').selectpicker('refresh');
            totalCostCalculation(tab);
        },
    });
}
function setRowValue(tab,row){
    $('#production_'+tab+'_packaging_materials_'+row+'_category').val($('#production_'+tab+'_packaging_materials_'+row+'_material_id option:selected').data('category'));
    $('#production_'+tab+'_packaging_materials_'+row+'_unit_id').val($('#production_'+tab+'_packaging_materials_'+row+'_material_id option:selected').data('unitid'));
    $('#production_'+tab+'_packaging_materials_'+row+'_unit_name').val($('#production_'+tab+'_packaging_materials_'+row+'_material_id option:selected').data('unitname'));
    $('#production_'+tab+'_packaging_materials_'+row+'_rate').val($('#production_'+tab+'_packaging_materials_'+row+'_material_id option:selected').data('rate'));
}

function calculateRowTotal(tab,row)
{
    var rate = $('#production_'+tab+'_packaging_materials_'+row+'_rate').val();
    var qty = $('#production_'+tab+'_packaging_materials_'+row+'_qty').val();
    var total  = 0;
    if(rate > 0 && qty > 0)
    {
        total = parseFloat(rate * qty).toFixed(4);
        $('#production_'+tab+'_packaging_materials_'+row+'_total').val(total);
    }else{
        $('#production_'+tab+'_packaging_materials_'+row+'_total').val('');
    }
    totalCostCalculation(tab);
}

function totalCostCalculation(tab)
{

    var total_cost = 0;
    var total_fg_qty = 0;
    if($('#production_'+tab+'_total_fg_qty').val()){
        total_fg_qty = $('#production_'+tab+'_total_fg_qty').val();
    }else{
        $('#production_'+tab+'_total_fg_qty').val(1);
        total_fg_qty = 1;
    }
    console.log(tab+' === '+total_fg_qty);
    totalMaterialCalculation(tab,total_fg_qty);
    $('#production_'+tab+' .total').each(function() {
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
    console.log(total_cost);
    totalNetCalculation(tab,total_fg_qty,total_cost);
}

function totalNetCalculation(tab,total_fg_qty,total_cost)
{
    $('#production_'+tab+'_total_cost').val(total_cost.toFixed(4));
    var extra_cost = 0;
    if($('#production_'+tab+'_extra_cost').val())
    {
        extra_cost = parseFloat($('#production_'+tab+'_extra_cost').val());
    }
    $('#production_'+tab+'_total_net_cost').val(parseFloat(total_cost + extra_cost).toFixed(4));
    var per_unit_cost = total_fg_qty > 0 ? parseFloat((total_cost + extra_cost) / total_fg_qty) : 0;
    $('#production_'+tab+'_per_unit_cost').val(parseFloat(per_unit_cost).toFixed(4));
    
}
function totalMaterialCalculation(tab,total_fg_qty)
{
    if(total_fg_qty){
        $('#material_table_'+tab+' .per_unit_total').each(function() {
            if($(this).val()){
                var trow = $(this).data('id');
                var ttotal = total_fg_qty * $(this).val();
                $('#material_table_'+tab+' #production_'+tab+'_materials_'+trow+'_total').val(parseFloat(ttotal).toFixed(4));
            }
        });
        $('#material_table_'+tab+' .per_unit_qty').each(function() {
            if($(this).val()){
                var prow = $(this).data('id');
                var ptotal = total_fg_qty * $(this).val();
                $('#material_table_'+tab+' #production_'+tab+'_materials_'+prow+'_qty').val(parseFloat(ptotal));
            }
        });
    }
}
function showFormulationData(type,tab)
{
    var formulation_no = $('#production_'+tab+'_formulation_no option:selected').val();
    var product_id = $('#production_'+tab+'_product_id option:selected').val();
    if(product_id)
    {
        $.ajax({
            url:"{{ url('product-mixing-formulation-data') }}",
            data:{product_id:product_id,formulation_id:formulation_no,type:type,_token:_token},
            type:"POST",
            beforeSend: function(){
                $('#production_'+tab+' #'+type+'-btn').addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#production_'+tab+' #'+type+'-btn').removeClass('spinner spinner-white spinner-right');
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
function check_material_stock(tab)
{
    $('#check_tab').val(tab);
    var rownumber = $('#material_table_'+tab+' tbody tr').length;
    if (rownumber == 0) {
        notification("error","Please insert material to table!")
    }else{
        if($('#production_'+tab+'_mwarehouse_id').val()){
            let form = document.getElementById('store_or_update_form');
            let formData = new FormData(form);
            let url = "{{url('pre-production-check-material-stock')}}";
            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function(){
                    $('#check-btn-'+tab).addClass('spinner spinner-white spinner-right');
                },
                complete: function(){
                    $('#check-btn-'+tab).removeClass('spinner spinner-white spinner-right');
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
    let url = "{{url('pre-production/store')}}";
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
                    window.location.replace("{{ url('pre-production-list') }}");
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