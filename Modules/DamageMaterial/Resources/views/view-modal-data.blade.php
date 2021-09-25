<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-borderless">
            <tr>
                <td><b>Date</b></td>
                <td><b>:</b></td>
                <td>{{ date(config('settings.date_format'),strtotime($material->damage_date)) }}</td>

                <td><b>Warehouse</b></td>
                <td><b>:</b></td>
                <td>{{ $material->warehouse->name }}</td>
            </tr>
            <tr>
                <td><b>Material Name</b></td>
                <td><b>:</b></td>
                <td>{{ $material->material->material_name }}</td>

                <td><b>Material Code</b></td>
                <td><b>:</b></td>
                <td>{{ $material->material->material_code }}</td>
            </tr>
            <tr>
                <td><b>Unit</b></td>
                <td><b>:</b></td>
                <td>{{ $material->unit->unit_name }}</td>

                <td><b>Damage Quantity</b></td>
                <td><b>:</b></td>
                <td>{{ number_format($material->qty,4,'.','') }}</td>
            </tr>
            <tr>
                <td><b>Net Unit Cost</b></td>
                <td><b>:</b></td>
                <td>{{ number_format($material->net_unit_cost,4,'.','') }}</td>

                <td><b>Total Damage Cost</b></td>
                <td><b>:</b></td>
                <td>{{ number_format($material->total,4,'.','') }}</td>
            </tr>

            <tr>
                <td><b>Created By</b></td>
                <td><b>:</b></td>
                <td>{{  $material->creator->name  }}</td>

                <td><b>Modified By</b></td>
                <td><b>:</b></td>
                <td>{{  $material->modified_by ? $material->modifier->name : ''  }}</td>
            </tr>
            <tr>
                <td><b>Create Date</b></td>
                <td><b>:</b></td>
                <td>{{  $material->created_at ? date(config('settings.date_format'),strtotime($material->created_at)) : ''  }}
                </td>

                <td><b>Modified Date</b></td>
                <td><b>:</b></td>
                <td>{{  $material->updated_at ? date(config('settings.date_format'),strtotime($material->updated_at)) : ''  }}
                </td>
            </tr>
        </table>
    </div>
</div>

