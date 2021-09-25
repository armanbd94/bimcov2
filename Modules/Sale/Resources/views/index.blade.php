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
                    <a href="{{ route('sale.add') }}"  class="btn btn-primary btn-sm font-weight-bolder"> 
                        <i class="fas fa-plus-circle"></i> Add New</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-header flex-wrap py-5">
                <form method="POST" id="form-filter" class="col-md-12 px-0">
                    <div class="row">
                        <x-form.textbox labelName="Invoice No." name="invoice_no" col="col-md-3" />
                        <div class="form-group col-md-3">
                            <label for="from_date">From Date</label>
                            <input type="text" class="form-control date" name="from_date" id="from_date" readonly />
                        </div>
                        <div class="form-group col-md-3">
                            <label for="to_date">To Date</label>
                            <input type="text" class="form-control date" name="to_date" id="to_date" readonly />
                        </div>
                        <x-form.selectbox labelName="Warehouse" name="warehouse_id" col="col-md-3" class="selectpicker">
                            @if (!$warehouses->isEmpty())
                                @foreach ($warehouses as $value)
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            @endif
                        </x-form.selectbox>

                        <x-form.selectbox labelName="Customer" name="customer_id" col="col-md-3" class="selectpicker">
                            @if (!$customers->isEmpty())
                                @foreach ($customers as $value)
                                    <option value="{{ $value->id }}">{{ $value->name.' - '.$value->mobile }}</option>
                                @endforeach
                            @endif
                        </x-form.selectbox>

                        <x-form.selectbox labelName="Payment Status" name="payment_status" col="col-md-3" class="selectpicker">
                            @foreach (PAYMENT_STATUS as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Approval Status" name="status" col="col-md-3" class="selectpicker">
                            @foreach (APPROVE_STATUS as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Delivery Status" name="delivery_status" col="col-md-3" class="selectpicker">
                            @foreach (DELIVERY_STATUS as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </x-form.selectbox>

                        <div class="col-md-12">
                            <div style="margin-top:28px;">    
                                <div style="margin-top:28px;">    
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="Reset">
                                    <i class="fas fa-undo-alt"></i></button>
    
                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="Search">
                                    <i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="table-responsive">
                                <table id="dataTable" class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                        <tr>
                                            @if (permission('sale-bulk-delete'))
                                            <th>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="select_all" onchange="select_all()">
                                                    <label class="custom-control-label" for="select_all"></label>
                                                </div>
                                            </th>
                                            @endif
                                            <th>Sl</th>
                                            <th>Invoice No.</th>
                                            <th>Warehouse</th>
                                            <th>Customer Name</th>
                                            <th>Total Item</th>
                                            <th>Total</th>
                                            {{-- <th>Discount</th> --}}
                                            {{-- <th>Labor Cost</th>
                                            <th>Tax Rate(%)</th>
                                            <th>Order Tax</th> --}}
                                            <th>Shippping Cost</th>
                                            <th>Grand Total</th>
                                            <th>Previous Due</th>
                                            <th>Net Total</th>
                                            <th>Receive Amount</th>
                                            <th>Due Amount</th>
                                            <th>Sale Date</th>
                                            <th>Payment Status</th>
                                            {{-- <th>Approval Status</th> --}}
                                            <th>Payment Method</th>
                                            <th>Delivery Status</th>
                                            <th>Delivery Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@include('sale::delivery-modal')
@endsection

@push('scripts')
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
var table;
$(document).ready(function(){
    $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});
    table = $('#dataTable').DataTable({
        "processing": true, //Feature control the processing indicator
        "serverSide": true, //Feature control DataTable server side processing mode
        "order": [], //Initial no order
        "responsive": false, //Make table responsive in mobile device
        "bInfo": true, //TO show the total number of data
        "bFilter": false, //For datatable default search box show/hide
        "lengthMenu": [
            [5, 10, 15, 25, 50, 100, 1000, 10000, -1],
            [5, 10, 15, 25, 50, 100, 1000, 10000, "All"]
        ],
        "pageLength": 25, //number of data show per page
        "language": { 
            processing: `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
            emptyTable: '<strong class="text-danger">No Data Found</strong>',
            infoEmpty: '',
            zeroRecords: '<strong class="text-danger">No Data Found</strong>'
        },
        "ajax": {
            "url": "{{route('sale.datatable.data')}}",
            "type": "POST",
            "data": function (data) {
                data.invoice_no       = $("#form-filter #invoice_no").val();
                data.from_date       = $("#form-filter #from_date").val();
                data.to_date         = $("#form-filter #to_date").val();
                data.warehouse_id     = $("#form-filter #warehouse_id option:selected").val();
                data.customer_id     = $("#form-filter #customer_id option:selected").val();
                data.payment_status  = $("#form-filter #payment_status option:selected").val();
                data.status          = $("#form-filter #status option:selected").val();
                data.delivery_status = $("#form-filter #delivery_status option:selected").val();
                data._token          = _token;
            }
        },
        "columnDefs": [{
                @if(permission('sale-bulk-delete'))
                "targets": [0,18],
                @else
                "targets": [17],
                @endif
                "orderable": false,
                "className": "text-center"
            },
            {
                @if(permission('sale-bulk-delete'))
                "targets": [1,2,3,5,13,14,15,16,17],
                @else
                "targets": [0,1,2,4,12,13,14,15,16],
                @endif
                "className": "text-center"
            },
            {

                @if(permission('sale-bulk-delete'))
                "targets": [6,7,8,9,10,11,12],
                @else
                "targets": [5,6,7,8,9,10,11],
                @endif
                "className": "text-right"
            },

        ],
        "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",

        "buttons": [
            @if(permission('sale-report'))
            {
                'extend':'colvis','className':'btn btn-secondary btn-sm text-white','text':'Column','columns': ':gt(0)'
            },
            {
                "extend": 'print',
                'text':'Print',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "orientation": "landscape", //portrait
                "pageSize": "legal", //A3,A5,A6,legal,letter
                "exportOptions": {
                    @if(permission('sale-bulk-delete'))
                    columns: ':visible:not(:eq(0),:eq(18))' 
                    @else
                    columns: ':visible:not(:eq(17))' 
                    @endif
                },
                customize: function (win) {
                    $(win.document.body).addClass('bg-white');
                    $(win.document.body).find('table thead').css({'background':'#034d97'});
                    $(win.document.body).find('table tfoot tr').css({'background-color':'#034d97'});
                    $(win.document.body).find('h1').css('text-align', 'center');
                    $(win.document.body).find('h1').css('font-size', '15px');
                    $(win.document.body).find('table').css( 'font-size', 'inherit' );
                },
            },
            {
                "extend": 'csv',
                'text':'CSV',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "exportOptions": {
                    @if(permission('sale-bulk-delete'))
                    columns: ':visible:not(:eq(0),:eq(18))' 
                    @else
                    columns: ':visible:not(:eq(17))' 
                    @endif
                }
            },
            {
                "extend": 'excel',
                'text':'Excel',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "exportOptions": {
                    @if(permission('sale-bulk-delete'))
                    columns: ':visible:not(:eq(0),:eq(18))' 
                    @else
                    columns: ':visible:not(:eq(17))' 
                    @endif
                }
            },
            {
                "extend": 'pdf',
                'text':'PDF',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "orientation": "landscape", //portrait
                "pageSize": "legal", //A3,A5,A6,legal,letter
                "exportOptions": {
                    @if(permission('sale-bulk-delete'))
                    columns: ':visible:not(:eq(0),:eq(18))' 
                    @else
                    columns: ':visible:not(:eq(17))' 
                    @endif
                },
                customize: function(doc) {
                    doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10 
                    doc.styles.tableHeader.fontSize = 7;
                    doc.pageMargins = [5,5,5,5];
                }  
            },
            @endif 
            @if (permission('sale-bulk-delete'))
            {
                'className':'btn btn-danger btn-sm delete_btn d-none text-white',
                'text':'Delete',
                action:function(e,dt,node,config){
                    multi_delete();
                }
            }
            @endif
        ],
    });

    $('#btn-filter').click(function () {
        table.ajax.reload();
    });

    $('#btn-reset').click(function () {
        $('#form-filter')[0].reset();
        $('#form-filter .selectpicker').selectpicker('refresh');
        table.ajax.reload();
    });

    //Add Delivery
    $(document).on('click', '.add_delivery', function () {
        let id              = $(this).data('id');
        let delivery_status = $(this).data('status');
        let delivery_date   = $(this).data('date');

        $('#delivery_form')[0].reset();
        $('#delivery_form').find('.is-invalid').removeClass('is-invalid');
        $('#delivery_form').find('.error').remove();
        $('.selectpicker').selectpicker('refresh');
        $('#delivery_modal #sale_id').val(id);
        $('#delivery_modal #delivery_status').val(delivery_status);
        $('#delivery_modal #delivery_date').val(delivery_date);
        $('.selectpicker').selectpicker('refresh');
        $('#delivery_modal').modal({
            keyboard: false,
            backdrop: 'static',
        });
        $('#delivery_modal .modal-title').html( '<i class="fas fa-truck"></i> <span>Add Delivery</span>');  
    });

    $(document).on('click','#delivery-save-btn', function(e){
        e.preventDefault();
        let form = document.getElementById('delivery_form');
        let formData = new FormData(form);
        $.ajax({
            url: "{{route('sale.delivery.update')}}",
            type: "POST",
            data: formData,
            dataType: "JSON",
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function(){
                $('#delivery-save-btn').addClass('kt-spinner kt-spinner--md kt-spinner--light');
            },
            complete: function(){
                $('#delivery-save-btn').removeClass('kt-spinner kt-spinner--md kt-spinner--light');
            },
            success: function (data) {
                $('#delivery_form').find('.is-invalid').removeClass('is-invalid');
                $('#delivery_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#delivery_form input#' + key).addClass('is-invalid');
                        $('#delivery_form select#' + key).parent().addClass('is-invalid');
                        $('#delivery_form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');
                                                    
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        table.ajax.reload(null, false);
                        $('#delivery_modal').modal('hide');
                    }
                }

            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
        
    });

    $(document).on('click', '.delete_data', function () {
        let id    = $(this).data('id');
        let name  = $(this).data('name');
        let row   = table.row($(this).parent('tr'));
        let url   = "{{ route('sale.delete') }}";
        delete_data(id, url, table, row, name);
    });

    function multi_delete(){
        let ids = [];
        let rows;
        $('.select_data:checked').each(function(){
            ids.push($(this).val());
            rows = table.rows($('.select_data:checked').parents('tr'));
        });
        if(ids.length == 0){
            Swal.fire({
                type:'error',
                title:'Error',
                text:'Please checked at least one row of table!',
                icon: 'warning',
            });
        }else{
            let url = "{{route('sale.bulk.delete')}}";
            bulk_delete(ids,url,table,rows);
        }
    }

     //Show Status Change Modal
    $(document).on('click','.change_status',function(){
        $('#approve_status_form #sale_id').val($(this).data('id'));
        $('#approve_status_form #sale_status').val($(this).data('status'));
        $('#approve_status_form #sale_status.selectpicker').selectpicker('refresh');
        $('#approve_status_modal').modal({
            keyboard: false,
            backdrop: 'static',
        });
        $('#approve_status_modal .modal-title').html('<span>Change Sale Status</span>');
        $('#approve_status_modal #status-btn').text('Change Status');
                
    });

    $(document).on('click','#status-btn',function(){
        var sale_id     = $('#approve_status_form #sale_id').val();
        var sale_status =  $('#approve_status_form #sale_status option:selected').val();
        if(sale_id && sale_status)
        {
            $.ajax({
                url: "{{route('sale.change.status')}}",
                type: "POST",
                data: {sale_id:sale_id,sale_status:sale_status,_token:_token},
                dataType: "JSON",
                beforeSend: function(){
                    $('#status-btn').addClass('');
                },
                complete: function(){
                    $('#status-btn').removeClass('');
                },
                success: function (data) {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        $('#approve_status_modal').modal('hide');
                        table.ajax.reload(null, false);
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }
    });



});
</script>
@endpush