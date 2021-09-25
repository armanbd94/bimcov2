@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css" />
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
                    @if (permission('product-mixing-add'))
                    <a href="{{ route('product.mixing.add') }}"  class="btn btn-primary btn-sm font-weight-bolder"> 
                        <i class="fas fa-plus-circle"></i> Add New</a>
                    @endif
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
                        <x-form.textbox labelName="Batch No." name="batch_no" col="col-md-3" />
                        <div class="form-group col-md-3">
                            <label for="name">Choose Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control daterangepicker-filed">
                                <input type="hidden" id="start_date" name="start_date" >
                                <input type="hidden" id="end_date" name="end_date" >
                            </div>
                        </div>
                        <x-form.selectbox labelName="FG Name" name="product_id" col="col-md-3" class="selectpicker">
                            @if (!$products->isEmpty())
                                @foreach ($products as $value)
                                    <option value="{{ $value->id }}">{{ $value->name.' - '.$value->code }}</option>
                                @endforeach
                            @endif
                        </x-form.selectbox>

                        <x-form.selectbox labelName="Approve Status" name="status" col="col-md-3" class="selectpicker">
                            @foreach (APPROVE_STATUS as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Production Status" name="production_status" col="col-md-3" class="selectpicker">
                            @foreach (PRODUCTION_STATUS as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </x-form.selectbox>
                        <div class="col-md-9">   
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
                                            @if (permission('product-mixing-bulk-delete'))
                                            <th>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="select_all" onchange="select_all()">
                                                    <label class="custom-control-label" for="select_all"></label>
                                                </div>
                                            </th>
                                            @endif
                                            <th>Sl</th>
                                            <th>Batch No.</th>
                                            <th>Date</th>
                                            <th>Finish Goods</th>
                                            <th>Serial No.</th>
                                            <th>FG Serial No.</th>
                                            <th>Unit</th>
                                            <th>Total FG Qty</th>
                                            <th>Total Cost</th>
                                            <th>Extra Cost</th>
                                            <th>Total Net Cost</th>
                                            <th>Per Unit Cost</th>
                                            <th>Approve Status</th>
                                            <th>Production Status</th>
                                            <th>Reference No</th>
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

@include('production::production.view-modal')
@include('production::production.status-modal')
@include('production::production.production-status-modal')
@endsection

@push('scripts')
<script src="js/knockout-3.4.2.js"></script>
<script src="js/daterangepicker.min.js"></script>
<script>
var table;
$(document).ready(function(){
    $('.daterangepicker-filed').daterangepicker({
        callback: function(startDate, endDate, period){
            var start_date = startDate.format('YYYY-MM-DD');
            var end_date   = endDate.format('YYYY-MM-DD');
            var title = start_date + ' To ' + end_date;
            $(this).val(title);
            $('input[name="start_date"]').val(start_date);
            $('input[name="end_date"]').val(end_date);
        }
    });
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
            "url": "{{route('product.mixing.datatable.data')}}",
            "type": "POST",
            "data": function (data) {
                data.batch_no          = $("#form-filter #batch_no").val();
                data.start_date        = $("#form-filter #start_date").val();
                data.end_date          = $("#form-filter #end_date").val();
                data.product_id        = $("#form-filter #product_id").val();
                data.status            = $("#form-filter #status").val();
                data.production_status = $("#form-filter #production_status").val();
                data._token            = _token;
            }
        },
        "columnDefs": [{
                @if(permission('product-mixing-bulk-delete'))
                "targets": [0,16],
                @else
                "targets": [15],
                @endif
                "orderable": false,
                "className": "text-center"
            },
            {
                @if(permission('product-mixing-bulk-delete'))
                "targets": [1,2,3,5,6,7,8,13,14,15],
                @else
                "targets": [0,1,2,4,5,6,7,12,13,14],
                @endif
                "className": "text-center"
            },
            {
                @if(permission('product-mixing-bulk-delete'))
                "targets": [9,10,11,12],
                @else
                "targets": [8,9,10,11],
                @endif
                "className": "text-right"
            },

        ],
        "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",

        "buttons": [
            @if(permission('product-mixing-report'))
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
                    @if(permission('product-mixing-bulk-delete'))
                    columns: ':visible:not(:eq(0),:eq(16))' 
                    @else
                    columns: ':visible:not(:eq(15))' 
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
                    @if(permission('product-mixing-bulk-delete'))
                    columns: ':visible:not(:eq(0),:eq(16))' 
                    @else
                    columns: ':visible:not(:eq(15))' 
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
                    @if(permission('product-mixing-bulk-delete'))
                    columns: ':visible:not(:eq(0),:eq(16))' 
                    @else
                    columns: ':visible:not(:eq(15))' 
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
                    @if(permission('product-mixing-bulk-delete'))
                    columns: ':visible:not(:eq(0),:eq(16))' 
                    @else
                    columns: ':visible:not(:eq(15))' 
                    @endif
                },
                customize: function(doc) {
                    doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10 
                    doc.styles.tableHeader.fontSize = 7;
                    doc.pageMargins = [5,5,5,5];
                }  
            },
            @endif 
            @if (permission('product-mixing-bulk-delete'))
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
        $('input[name="start_date"]').val('');
        $('input[name="end_date"]').val('');
        $('#form-filter .selectpicker').selectpicker('refresh');
        table.ajax.reload();
    });

    $(document).on('click', '.view_data', function () {
        let id = $(this).data('id');
        if (id) {
            $.ajax({
                url: "{{url('formulation/view')}}",
                type: "POST",
                data: { id: id,_token: _token},
                success: function (data) {
                    $('#view_modal #view-data').html('');
                    $('#view_modal #view-data').html(data);
                    $('#view_modal').modal({
                        keyboard: false,
                        backdrop: 'static',
                    });
                    $('#view_modal .modal-title').html('<i class="fas fa-eye text-white"></i> <span> Formulation Details</span>');
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }
    });

    $(document).on('click', '.delete_data', function () {
        let id    = $(this).data('id');
        let name  = $(this).data('name');
        let row   = table.row($(this).parent('tr'));
        let url   = "{{ route('product.mixing.delete') }}";
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
            let url = "{{route('product.mixing.bulk.delete')}}";
            bulk_delete(ids,url,table,rows);
        }
    }

     //Show Approve Status Change Modal
     $(document).on('click','.change_status',function(){
        $('#approve_status_form #production_id').val($(this).data('id'));
        $('#approve_status_form #approve_status').val($(this).data('status'));
        $('#approve_status_form #approve_status.selectpicker').selectpicker('refresh');
        $('#approve_status_modal').modal({
            keyboard: false,
            backdrop: 'static',
        });
        $('#approve_status_modal .modal-title').html('<span>Change Approve Status</span>');
        $('#approve_status_modal #status-btn').text('Change Status');
            
    });

    $(document).on('click','#status-btn',function(){
        var production_id     = $('#approve_status_form #production_id').val();
        var approve_status =  $('#approve_status_form #approve_status option:selected').val();
        if(production_id && approve_status)
        {
            $.ajax({
                url: "{{route('product.mixing.change.status')}}",
                type: "POST",
                data: {production_id:production_id,approve_status:approve_status,_token:_token},
                dataType: "JSON",
                beforeSend: function(){
                    $('#status-btn').addClass('spinner spinner-white spinner-right');
                },
                complete: function(){
                    $('#status-btn').removeClass('spinner spinner-white spinner-right');
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

    //Show Approve Status Change Modal
    $(document).on('click','.change_production_status',function(){
        $('#production_status_form #production_id').val($(this).data('id'));
        $('#production_status_form #production_status').val($(this).data('status'));
        $('#production_status_form #production_status.selectpicker').selectpicker('refresh');
        $('#production_status_modal').modal({
            keyboard: false,
            backdrop: 'static',
        });
        $('#production_status_modal .modal-title').html('<span>Change Production Status</span>');
        $('#production_status_modal #production-status-btn').text('Change Status');
            
    });

    $(document).on('click','#production-status-btn',function(){
        var production_id     = $('#production_status_form #production_id').val();
        var production_status =  $('#production_status_form #production_status option:selected').val();
        if(production_id && production_status)
        {
            $.ajax({
                url: "{{route('product.mixing.change.production.status')}}",
                type: "POST",
                data: {production_id:production_id,production_status:production_status,_token:_token},
                dataType: "JSON",
                beforeSend: function(){
                    $('#production-status-btn').addClass('spinner spinner-white spinner-right');
                },
                complete: function(){
                    $('#production-status-btn').removeClass('spinner spinner-white spinner-right');
                },
                success: function (data) {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        $('#production_status_modal').modal('hide');
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