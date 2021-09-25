@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link rel="stylesheet" href="css/jquery-ui.css" />
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
                    <a href="{{ route('transfer') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <form action="" id="transfer_store_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="transfer_id" value="{{ $transfer->id }}">
                        <div class="row">
                            <div class="form-group col-md-3 required">
                                <label for="chalan_no">Chalan No.</label>
                                <input type="text" class="form-control" name="chalan_no" id="chalan_no" value="{{ $transfer->chalan_no }}" readonly />
                            </div>
                            <x-form.textbox labelName="Transfer Date" name="transfer_date" value="{{ $transfer->transfer_date }}" required="required" class="date" col="col-md-3"/>

                            <x-form.selectbox labelName="From Warehouse" name="from_warehouse_id" required="required" col="col-md-3" class="selectpicker">
                                @if (!$warehouses->isEmpty())
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ $transfer->from_warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach 
                                @endif
                            </x-form.selectbox>

                            <x-form.selectbox labelName="To Warehouse" name="to_warehouse_id" required="required" col="col-md-3" class="selectpicker">
                                @if (!$warehouses->isEmpty())
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ $transfer->to_warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach 
                                @endif
                            </x-form.selectbox>

                            <div class="form-group col-md-12">
                                <label for="material_code_name">Select Material</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-barcode"></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="material_code_name" id="material_code_name" placeholder="Please type material code and select...">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <table class="table table-bordered" id="material_table">
                                    <thead class="bg-primary">
                                        <th>Name</th>
                                        <th class="text-center">Code</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Available Quantity</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-right">Net Unit Cost</th>
                                        <th class="text-right">Subtotal</th>
                                        <th></th>
                                    </thead>
                                    <tbody>
                                        @php
                                            $temp_unit_name = [];
                                            $temp_unit_operator = [];
                                            $temp_unit_operation_value = [];
                                        @endphp
                                        @if (!$transfer->transfer_materials->isEmpty())
                                            @foreach ($transfer->transfer_materials as $key => $transfer_material)
                                                <tr>
                                                    @php
                                                        $units = DB::table('units')->where('base_unit',$transfer_material->unit_id)
                                                                                    ->orWhere('id',$transfer_material->unit_id)
                                                                                    ->get();

                                                        $warehouse_qty = DB::table('warehouse_material')
                                                        ->where(['warehouse_id'=>$transfer->from_warehouse_id,'material_id'=>$transfer_material->id])->value('qty');
                                                        $stock_qty = $warehouse_qty ? $warehouse_qty : 0;

                                                        $unit_name            = [];
                                                        $unit_operator        = [];
                                                        $unit_operation_value = [];

                                                        if($units){
                                                            foreach ($units as $unit) {
                                                                if($transfer_material->pivot->transfer_unit_id == $unit->id)
                                                                {
                                                                    array_unshift($unit_name,$unit->unit_name);
                                                                    array_unshift($unit_operator,$unit->operator);
                                                                    array_unshift($unit_operation_value,$unit->operation_value);
                                                                }else{
                                                                    $unit_name           [] = $unit->unit_name;
                                                                    $unit_operator       [] = $unit->operator;
                                                                    $unit_operation_value[] = $unit->operation_value;
                                                                }
                                                            }
                                                            $temp_unit_name = $unit_name = implode(",",$unit_name).',';
                                                            $temp_unit_operator = $unit_operator = implode(",",$unit_operator).',';
                                                            $temp_unit_operation_value = $unit_operation_value = implode(",",$unit_operation_value).',';
                                                        }
                                                    @endphp
                                                    <td>{{ $transfer_material->material_name }}</td>
                                                    <td class="text-center">{{ $transfer_material->material_code }}</td>
                                                    <td class="unit-name text-center"></td>
                                                    <td class="text-center">{{ $stock_qty + $transfer_material->pivot->qty }}</td>
                                                    <td><input type="text" class="form-control qty text-center" name="materials[{{ $key + 1 }}][qty]" id="materials_{{ $key + 1 }}_qty" value="{{$transfer_material->pivot->qty}}"></td>
                                                    <td>{{ number_format($transfer_material->pivot->net_unit_cost,4,'.','') }}</td>
                                                    <td class="sub-total text-right">{{ number_format($transfer_material->pivot->total,4,'.','') }}</td>
                                                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-material small-btn"><i class="fas fa-trash"></i></button></td>
                                                    <input type="hidden" class="material-id" name="materials[{{ $key + 1 }}][id]"  value="{{ $transfer_material->id }}">
                                                    <input type="hidden"  name="materials[{{ $key + 1 }}][name]" value="{{ $transfer_material->material_name }}">
                                                    <input type="hidden" class="material-code" name="materials[{{ $key + 1 }}][code]" value="{{ $transfer_material->material_code }}">
                                                    <input type="hidden" class="material-stock-qty" name="materials[{{ $key + 1 }}][stock_qty]" value="{{ $stock_qty + $transfer_material->pivot->qty }}">
                                                    <input type="hidden" class="material-unit transfer-unit" name="materials[{{ $key + 1 }}][unit]" value="{{ $unit_name }}">
                                                    <input type="hidden" class="transfer-unit-operator"  value="{{ $unit_operator }}">
                                                    <input type="hidden" class="transfer-unit-operation-value"  value="{{ $unit_operation_value }}">
                                                    <input type="hidden" class="material-cost" name="materials[{{ $key + 1 }}][net_unit_cost]" value="{{ $transfer_material->pivot->net_unit_cost }}}">
                                                    <input type="hidden" class="subtotal-value" name="materials[{{ $key + 1 }}][subtotal]" value="{{ $transfer_material->pivot->total }}">
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot class="bg-primary">
                                            <th colspan="4" class="font-weight-bolder">Total</th>
                                            <th id="total-qty" class="text-center font-weight-bolder">{{ number_format($transfer->total_qty,4,'.','') }}</th>
                                            <th></th>
                                            <th id="total" class="text-right font-weight-bolder">{{ number_format($transfer->total_cost,4,'.','') }}</th>
                                            <th></th>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <div class="form-group col-md-4">
                                <label for="shipping_cost">Shipping Cost</label>
                                <input type="text" class="form-control" name="shipping_cost" id="shipping_cost" value="{{ number_format($transfer->shipping_cost,4,'.','') }}">
                            </div>

                            <div class="form-group col-md-4 required">
                                <label for="carried_by">Carried By</label>
                                <input type="text" class="form-control" name="carried_by" id="carried_by" value="{{ $transfer->carried_by }}">
                            </div>

                            <div class="form-group col-md-4 required">
                                <label for="received_by">Received By</label>
                                <input type="text" class="form-control" name="received_by" id="received_by"  value="{{ $transfer->received_by }}">
                            </div>

                            <div class="form-group col-md-12">
                                <label for="shipping_cost">Note</label>
                                <textarea  class="form-control" name="note" id="note" cols="30" rows="3">{{ $transfer->note }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <thead class="bg-primary">
                                        <th><strong>Items</strong><span class="float-right" id="item">{{ $transfer->item.'('.$transfer->total_qty.')' }}</span></th>
                                        <th><strong>Total</strong><span class="float-right" id="subtotal">{{ number_format($transfer->total_cost,4,'.','') }}</span></th>
                                        <th><strong>Shipping Cost</strong><span class="float-right" id="shipping_total_cost">{{ number_format($transfer->shipping_cost,4,'.','') }}</span></th>
                                        <th><strong>Grand Total</strong><span class="float-right" id="grand_total">{{ number_format($transfer->grand_total,4,'.','') }}</span></th>
                                    </thead>
                                </table>
                            </div>
                          
                            <div class="col-md-12">
                                <input type="hidden" name="total_qty" value="{{ $transfer->total_qty }}">
                                <input type="hidden" name="total_cost" value="{{ $transfer->total_cost }}">
                                <input type="hidden" name="item" value="{{ $transfer->item }}">
                                <input type="hidden" name="grand_total" value="{{ $transfer->grand_total }}">
                            </div>
                            <div class="form-grou col-md-12 text-center pt-5">
                                <button type="button" id="reset-btn" class="btn btn-danger btn-sm mr-3"><i class="fas fa-sync-alt"></i> Reset</button>
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection

@push('scripts')
<script src="js/jquery-ui.js"></script>
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
$(document).ready(function () {
    $('.date').datetimepicker({format: 'YYYY-MM-DD'});

    //array data depend on warehouse
    var material_array = [];
    var material_code  = [];
    var material_name  = [];
    var material_qty   = [];

    // array data with selection
    var material_cost        = [];
    var unit_name            = [];
    var unit_operator        = [];
    var unit_operation_value = [];

    //temporary array
    var temp_unit_name            = [];
    var temp_unit_operator        = [];
    var temp_unit_operation_value = [];

    var rowindex;
    var row_material_cost;

    var rownumber = $('#material_table tbody tr:last').index();
    for (rowindex = 0; rowindex <= rownumber; rowindex++) {
        material_cost.push(parseFloat($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.material-cost').val()));
        var quantity = parseFloat($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.qty').val())
        temp_unit_name = $('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.transfer-unit').val().split(',');
        unit_name.push($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.transfer-unit').val());
        unit_operator.push($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.transfer-unit-operator').val());
        unit_operation_value.push($('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.transfer-unit-operation-value').val());
        $('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.transfer-unit').val(temp_unit_name[0]);
        $('#material_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.unit-name').text(temp_unit_name[0]);
    }

    $('#material_code_name').on('input',function(){
        var warehouse_id  = $('#from_warehouse_id option:selected').val();
        var temp_data = $('#material_code_name').val();
        if(!warehouse_id)
        {
            $('#material_code_name').val(temp_data.substring(0,temp_data.length - 1));
            notification('error','Please select from warehouse!');
        }
    });

    $('#material_code_name').autocomplete({
        source: function( request, response ) {
          // Fetch data
          $.ajax({
            url:"{{url('material-warehouse-search')}}",
            type: 'post',
            dataType: "json",
            data: {
               _token: _token,
               search: request.term,
               warehouse_id:$('#from_warehouse_id option:selected').val()
            },
            success: function( data ) {
               response( data );
            }
          });
        },
        minLength: 3,
        response: function(event, ui) {
            if (ui.content.length == 1) {
                var data = ui.content[0].code;
                $(this).autocomplete( "close" );
                materialSearch(data);
            };
        },
        select: function (event, ui) {
            var data = ui.item.code;
            materialSearch(data);
        },
    }).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $("<li class='ui-autocomplete-row'></li>")
            .data("item.autocomplete", item)
            .append(item.label)
            .appendTo(ul);
    };

    $('#material_table').on('keyup','.qty',function(){
        rowindex = $(this).closest('tr').index();
        var stock_qty = $('#material_table tbody tr:nth-child('+(rowindex + 1)+') .material-stock-qty').val();
        if(parseFloat($(this).val()) < 1 && parseFloat($(this).val()) != ''){
            $('#material_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val(1);
            notification('error','Qunatity can\'t be less than 1');
        }else if(parseFloat($(this).val()) > parseFloat(stock_qty)){
            $('#material_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val(1);
            notification('error','Qunatity can\'t be greater than available stock quantity');
        }
        checkQuantity($(this).val(),true);
    });

    $('#material_table').on('click','.remove-material',function(){
        rowindex = $(this).closest('tr').index();
        material_cost.splice(rowindex,1);
        unit_name.splice(rowindex,1);
        unit_operator.splice(rowindex,1);
        unit_operation_value.splice(rowindex,1);
        $(this).closest('tr').remove();
        calculateTotal();
    });

    @if (!$transfer->transfer_materials->isEmpty())
    var count = "{{ count($transfer->transfer_materials) + 1 }}" ;
    @else 
    var count = 1;
    @endif
    function materialSearch(data) {
        $.ajax({
            url: '{{ route("transfer.material.search") }}',
            type: 'POST',
            data: {
                data: data,_token:_token, warehouse_id:$('#from_warehouse_id option:selected').val()
            },
            success: function(data) {
                var flag = 1;
                $('.material-code').each(function(i){
                    if($(this).val() == data.code){
                        rowindex = i;
                        var qty = parseFloat($('#material_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val()) + 1;
                        $('#material_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val(qty);
                        calculateProductData(qty);
                        flag = 0;
                    }
                });
                $('#material_code_name').val('');
                if(flag)
                {
                    temp_unit_name = data.unit_name.split(',');
                    var newRow = $('<tr>');
                    var cols = '';
                    cols += `<td>`+data.name+`</td>`;
                    cols += `<td class="text-center">`+data.code+`</td>`;
                    cols += `<td class="unit-name text-center"></td>`;
                    cols += `<td class="text-center">${data.qty}</td>`;
                    cols += `<td><input type="text" class="form-control qty text-center" name="materials[`+count+`][qty]"
                        id="materials_`+count+`_qty" value="1"></td>`;

                    cols += `<td>${data.cost}</td>`;
                    cols += `<td class="sub-total text-right"></td>`;
                    cols += `<td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-material small-btn"><i class="fas fa-trash"></i></button></td>`;
                    cols += `<input type="hidden" class="material-id" name="materials[`+count+`][id]"  value="`+data.id+`">`;
                    cols += `<input type="hidden"  name="materials[`+count+`][name]" value="`+data.name+`">`;
                    cols += `<input type="hidden" class="material-code" name="materials[`+count+`][code]" value="`+data.code+`">`;
                    cols += `<input type="hidden" class="material-stock-qty" name="materials[`+count+`][stock_qty]" value="`+data.qty+`">`;
                    cols += `<input type="hidden" class="material-unit" name="materials[`+count+`][unit]" value="`+temp_unit_name[0]+`">`;
                    cols += `<input type="hidden" class="material-cost" name="materials[`+count+`][net_unit_cost]" value="${data.cost}">`;
                    cols += `<input type="hidden" class="subtotal-value" name="materials[`+count+`][subtotal]">`;

                    newRow.append(cols);
                    $('#material_table tbody').append(newRow);

                    material_cost.push(parseFloat(data.cost));
                    unit_name.push(data.unit_name);
                    unit_operator.push(data.unit_operator);
                    unit_operation_value.push(data.unit_operation_value);
                    rowindex = newRow.index();
                    calculateProductData(1);
                    count++;
                }
                
            }
        });
    }

    function checkQuantity(transfer_qty,flag)
    {
        var row_material_code = $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(2)').text();
        var pos = material_code.indexOf(row_material_code);
        var operator = unit_operator[rowindex].split(',');
        var operation_value = unit_operation_value[rowindex].split(',');

        if(operator[0] == '*')
        {
            total_qty = transfer_qty * operation_value[0];
        }else if(operator[0] == '/'){
            total_qty = transfer_qty / operation_value[0];
        }
        $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.qty').val(transfer_qty);
        calculateProductData(transfer_qty);
    }

    function calculateProductData(quantity){ 
        unitConversion();
        var sub_total = row_material_cost * quantity;
        $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.unit-name').text(unit_name[rowindex].slice(0,unit_name[rowindex].indexOf(",")));
        $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.material-unit').val(unit_name[rowindex].slice(0,unit_name[rowindex].indexOf(",")));
        $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(7)').text(sub_total.toFixed(4));
        $('#material_table tbody tr:nth-child('+(rowindex + 1)+')').find('.subtotal-value').val(sub_total.toFixed(4));
        calculateTotal();
    }

    function unitConversion()
    {
        var row_unit_operator = unit_operator[rowindex].slice(0,unit_operator[rowindex].indexOf(','));
        var row_unit_operation_value = unit_operation_value[rowindex].slice(0,unit_operation_value[rowindex].indexOf(','));
        row_unit_operation_value = parseFloat(row_unit_operation_value);
        if(row_unit_operator == '*')
        {
            row_material_cost = material_cost[rowindex] * row_unit_operation_value;
        }else{
            row_material_cost = material_cost[rowindex] / row_unit_operation_value;
        }
    }

    function calculateTotal()
    {
        //sum of qty
        var total_qty = 0;
        $('.qty').each(function() {
            if($(this).val() == ''){
                total_qty += 0;
            }else{
                total_qty += parseFloat($(this).val());
            }
        });
        $('#total-qty').text(total_qty);
        $('input[name="total_qty"]').val(total_qty);

        //sum of subtotal
        var total = 0;
        $('.sub-total').each(function() {
            total += parseFloat($(this).text());
        });
        $('#total').text(total.toFixed(4));
        $('input[name="total_cost"]').val(total.toFixed(4));
        calculateGrandTotal();
    }

    function calculateGrandTotal()
    {
        var item = $('#material_table tbody tr:last').index();
        var total_qty = parseFloat($('#total-qty').text());
        var subtotal = parseFloat($('#total').text());
        var shipping_cost = parseFloat($('#shipping_cost').val());
        if(!shipping_cost){
            shipping_cost = 0.00;
        }
        item = ++item + '(' + total_qty + ')';
        var grand_total = subtotal + shipping_cost;
        $('#item').text(item);
        $('input[name="item"]').val($('#material_table tbody tr:last').index() + 1);
        $('#subtotal').text(subtotal.toFixed(4));
        $('#shipping_total_cost').text(shipping_cost.toFixed(4));
        $('#grand_total').text(grand_total.toFixed(4));
        $('input[name="grand_total"]').val(grand_total.toFixed(4));
    }

    $('input[name="shipping_cost"]').on('input',function(){
        calculateGrandTotal();
    });


    $('#reset-btn').click(function(){
        window.location.replace("{{ route('transfer.add') }}");
    });

});


function store_data(){
    var rownumber = $('table#material_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert material to order table!")
    }else{
        let form = document.getElementById('transfer_store_form');
        let formData = new FormData(form);
        let url = "{{route('transfer.update')}}";
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
                $('#transfer_store_form').find('.is-invalid').removeClass('is-invalid');
                $('#transfer_store_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#transfer_store_form input#' + key).addClass('is-invalid');
                        $('#transfer_store_form textarea#' + key).addClass('is-invalid');
                        $('#transfer_store_form select#' + key).parent().addClass('is-invalid');
                        $('#transfer_store_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ route('transfer') }}");
                    }
                }

            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }
}
</script>
@endpush