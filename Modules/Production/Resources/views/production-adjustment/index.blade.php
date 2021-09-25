@extends('layouts.app')

@section('title', $page_title)

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom gutter-b">
            <div class="card-header flex-wrap py-5">
                <div class="card-title">
                    <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-custom card-border">
                                <div class="card-body">
                                   <form class="form-inline" action="{{ url('production-adjustment/show') }}" method="GET">
                                        <div class="form-group">
                                            <label for="batch_no"> Batch No:</label>
                                            <input type="text" name="batch_no" class="form-control mx-3" id="batch_no" placeholder="Batch No" required="required">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">Serach</button>
                                    </form>
                                </div>
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
@endsection
