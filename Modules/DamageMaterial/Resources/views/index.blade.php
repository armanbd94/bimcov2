@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css" />
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
                    @if (permission('damage-material-add'))
                    <a href="javascript:void(0);" onclick="showNewFormModal('Add New Damage Material','Save')" class="btn btn-primary btn-sm font-weight-bolder"> 
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
                        <div class="form-group col-md-4">
                            <label for="name">Choose Your Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control daterangepicker-filed">
                                <input type="hidden" id="from_date" name="from_date" >
                                <input type="hidden" id="to_date" name="to_date" >
                            </div>
                        </div>
                        <x-form.selectbox labelName="Warehouse" name="warehouse_id" required="required" onchange="materialList(this.value,1)" col="col-md-4" class="selectpicker">
                            @if (!$warehouses->isEmpty())
                                @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Material" name="material_id" required="required" col="col-md-4" class="selectpicker"/>
                        <div class="col-md-12">
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
                            <table id="dataTable" class="table table-bordered table-hover">
                                <thead class="bg-primary">
                                    <tr>
                                        @if (permission('damage-material-bulk-delete'))
                                        <th>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="select_all" onchange="select_all()">
                                                <label class="custom-control-label" for="select_all"></label>
                                            </div>
                                        </th>
                                        @endif
                                        <th>Sl</th>
                                        <th>Material Name</th>
                                        <th>Material Code</th>
                                        <th>Warehouse</th>
                                        <th>Stock Unit</th>
                                        <th>Quantity</th>
                                        <th>Net Unit Cost</th>
                                        <th>Total Damage Cost</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@include('damagematerial::modal')
@include('damagematerial::view-modal')
@endsection

@push('scripts')
<script src="js/knockout-3.4.2.js"></script>
<script src="js/daterangepicker.min.js"></script>
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
    var table;
    $(document).ready(function(){
        $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});
        $('.daterangepicker-filed').daterangepicker({
            callback: function(startDate, endDate, period){
                var start_date = startDate.format('YYYY-MM-DD');
                var end_date   = endDate.format('YYYY-MM-DD');
                var title = start_date + ' To ' + end_date;
                $(this).val(title);
                $('input[name="from_date"]').val(start_date);
                $('input[name="to_date"]').val(end_date);
            }
        });
        table = $('#dataTable').DataTable({
            "processing": true, //Feature control the processing indicator
            "serverSide": true, //Feature control DataTable server side processing mode
            "order": [], //Initial no order
            "responsive": true, //Make table responsive in mobile device
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
                "url": "{{route('damage.material.datatable.data')}}",
                "type": "POST",
                "data": function (data) {
                    data.warehouse_id = $("#form-filter #warehouse_id").val();
                    data.material_id  = $("#form-filter #material_id").val();
                    data.from_date    = $("#form-filter #from_date").val();
                    data.to_date      = $("#form-filter #to_date").val();
                    data._token       = _token;
                }
            },
            "columnDefs": [{
                    @if (permission('damage-material-bulk-delete'))
                    "targets": [0,10],
                    @else 
                    "targets": [9],
                    @endif
                    "orderable": false,
                    "className": "text-center"
                },
                {
                    @if (permission('damage-material-bulk-delete'))
                    "targets": [1,3,4,5,6,9],
                    @else 
                    "targets": [0,2,3,4,5,8],
                    @endif
                    "className": "text-center"
                },
                {
                    @if (permission('damage-material-bulk-delete'))
                    "targets": [7,8],
                    @else 
                    "targets": [6,7],
                    @endif
                    "className": "text-right"
                }
            ],
            "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",
    
            "buttons": [
                {
                    'extend':'colvis','className':'btn btn-secondary btn-sm text-white','text':'Column','columns': ':gt(0)'
                },
                {
                    "extend": 'print',
                    'text':'Print',
                    'className':'btn btn-secondary btn-sm text-white',
                    "title": "{{ $page_title }} List",
                    "orientation": "landscape", //portrait
                    "pageSize": "A4", //A3,A5,A6,legal,letter
                    "exportOptions": {
                        @if (permission('damage-material-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(10))' 
                        @else 
                        columns: ':visible:not(:eq(9))' 
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
                        @if (permission('damage-material-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(10))' 
                        @else 
                        columns: ':visible:not(:eq(9))' 
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
                        @if (permission('damage-material-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(10))' 
                        @else 
                        columns: ':visible:not(:eq(9))' 
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
                    "pageSize": "A4", //A3,A5,A6,legal,letter
                    "exportOptions": {
                        @if (permission('damage-material-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(10))' 
                        @else 
                        columns: ':visible:not(:eq(9))' 
                        @endif
                    },
                    customize: function(doc) {
                        doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10 
                        doc.styles.tableHeader.fontSize = 7;
                        doc.pageMargins = [5,5,5,5];
                    } 
                },
                @if (permission('damage-material-bulk-delete'))
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
            $('#form-filter #material_id').empty();
            $('#form-filter .selectpicker').selectpicker('refresh');
            table.ajax.reload();
        });

        $(document).on('click', '#save-btn', function () {
            let form = document.getElementById('store_or_update_form');
            let formData = new FormData(form);
            let url = "{{route('damage.material.store.or.update')}}";
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
                    $('#save-btn').addClass('spinner spinner-white spinner-right');
                },
                complete: function(){
                    $('#save-btn').removeClass('spinner spinner-white spinner-right');
                },
                success: function (data) {
                    $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
                    $('#store_or_update_form').find('.error').remove();
                    if (data.status == false) {
                        $.each(data.errors, function (key, value) {
                            $('#store_or_update_form input#' + key).addClass('is-invalid');
                            $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                            $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                            if(key == 'material_code'){
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
                            if (method == 'update') {
                                table.ajax.reload(null, false);
                            } else {
                                table.ajax.reload();
                            }
                            $('#store_or_update_form')[0].reset();
                            $('#store_or_update_form .selectpicker').val('');
                            $('#store_or_update_form #purchase_unit_id').empty();
                            $('#store_or_update_form .selectpicker').selectpicker('refresh');
                            $('#store_or_update_modal').modal('hide');
                        }
                    }

                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        });
    
        $(document).on('click', '.edit_data', function () {
            let id = $(this).data('id');
            $('#store_or_update_form')[0].reset();
            $('#store_or_update_form .select').val('');
            $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
            $('#store_or_update_form').find('.error').remove();
            if (id) {
                $.ajax({
                    url: "{{route('damage.material.edit')}}",
                    type: "POST",
                    data: { id: id,_token: _token},
                    dataType: "JSON",
                    success: function (data) {
                        if(data.status == 'error'){
                            notification(data.status,data.message)
                        }else{
                            let stock_qty = parseFloat(data.stock_qty) + parseFloat(data.qty);
                            $('#store_or_update_form #update_id').val(data.id);
                            $('#store_or_update_form #warehouse_id').val(data.warehouse_id);
                            $('#store_or_update_form #unit_id').val(data.unit_id);
                            $('#store_or_update_form #unit_name').val(data.unit_name);
                            $('#store_or_update_form #damage_date').val(data.date);
                            $('#store_or_update_form #qty').val(parseFloat(data.qty).toFixed(4));
                            $('#store_or_update_form #stock_qty').val(parseFloat(stock_qty).toFixed(4));
                            $('#store_or_update_form #net_unit_cost').val(parseFloat(data.net_unit_cost).toFixed(4));
                            $('#store_or_update_form #total').val(parseFloat(data.total).toFixed(4));


                            materialList(data.warehouse_id,2,data.material_id);
                            $('#store_or_update_modal').modal({
                                keyboard: false,
                                backdrop: 'static',
                            });
                            $('#store_or_update_modal .modal-title').html(
                                '<i class="fas fa-edit text-white"></i> <span>Edit Damage Material</span>');
                            $('#store_or_update_modal #save-btn').text('Update');
                        }
                        
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            }
        });

        $(document).on('click', '.view_data', function () {
            let id = $(this).data('id');
            let name  = $(this).data('name');
            if (id) {
                $.ajax({
                    url: "{{route('damage.material.view')}}",
                    type: "POST",
                    data: { id: id,_token: _token},
                    success: function (data) {
                        $('#view_modal #view-data').html('');
                        $('#view_modal #view-data').html(data);
                        $('#view_modal').modal({
                            keyboard: false,
                            backdrop: 'static',
                        });
                        $('#view_modal .modal-title').html(
                                '<i class="fas fa-eye text-white"></i> <span> ' + name + ' Details</span>');
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
            let url   = "{{ route('damage.material.delete') }}";
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
                let url = "{{route('damage.material.bulk.delete')}}";
                bulk_delete(ids,url,table,rows);
            }
        }
    
        $(document).on('change', '#store_or_update_form #material_id', function () {
            $('#store_or_update_form #unit_id').val($('#store_or_update_form #material_id option:selected').data('unitid'));
            $('#store_or_update_form #unit_name').val($('#store_or_update_form #material_id option:selected').data('unitname'));
            $('#store_or_update_form #stock_qty').val(parseFloat($('#store_or_update_form #material_id option:selected').data('qty')).toFixed(4));
            $('#store_or_update_form #net_unit_cost').val(parseFloat($('#store_or_update_form #material_id option:selected').data('cost')).toFixed(4));
        });
        $(document).on('keyup', '#store_or_update_form #qty', function () {
            let qty = parseFloat($(this).val());
            let stock_qty = parseFloat($('#store_or_update_form #stock_qty').val());
            let cost = parseFloat($('#store_or_update_form #net_unit_cost').val());

            if(qty < 0)
            {
                notification('error','Damage quantity must be greater than zero!');
                $(this).val(1);
                qty = 1;
            }else if(qty > stock_qty){
                notification('error','Damage quantity must be less than or equal to stock quantity!');
                $(this).val(0);
                qty = 0;
            }
            let total = qty * cost;
            $('#store_or_update_form #total').val(parseFloat(total).toFixed(4));
        });
    });
    
    function materialList(warehouse_id,set_id,material_id='')
    {
        $.ajax({
            url:"{{ url('warehouse-wise-materials') }}",
            type:"POST",
            data:{warehouse_id:warehouse_id,_token:_token},
            success:function(data){
                if(set_id == 1)
                {
                    $('#form-filter #material_id').empty().html(data);
                }else{
                    $('#store_or_update_form #material_id').empty().html(data);
                }
                $('.selectpicker').selectpicker('refresh');
                if(material_id)
                {
                    $('#store_or_update_form #material_id').val(material_id);
                    $('#store_or_update_form #material_id.selectpicker').selectpicker('refresh');
                }
            },
        });
    }

    function showNewFormModal(modal_title, btn_text) {
        $('#store_or_update_form')[0].reset();
        $('#store_or_update_form #update_id').val('');
        $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
        $('#store_or_update_form').find('.error').remove();

        $('store_or_update_form #warehouse_id').val('');
        $('store_or_update_form #material_id').empty();
        $('#store_or_update_form .selectpicker').selectpicker('refresh');

        $('#store_or_update_modal').modal({
            keyboard: false,
            backdrop: 'static',
        });
        $('#store_or_update_modal .modal-title').html('<i class="fas fa-plus-square text-white"></i> '+modal_title);
        $('#store_or_update_modal #save-btn').text(btn_text);
    }

    </script>
@endpush