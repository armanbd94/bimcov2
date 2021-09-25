@if (!$formulations->isEmpty())
    @foreach ($formulations as $formulation)
        <div class="col-md-12 text-center"><h3 class="py-3 {{ $formulation->status == 1 ? 'bg-success' : 'bg-danger' }} text-white" style="max-width:300px;margin: 10px auto 10px auto;">FG Serial : {{ $formulation->id }}</h3></div>
        <table class="table table-bordered pb-5" id="material_table">
            <thead class="bg-primary">
                <th width="40%">Material</th>
                <th width="10%" class="text-center">Category</th>
                <th width="10%" class="text-center">Unit Name</th>
                <th width="10%" class="text-right">RM Rate</th>
                <th width="10%" class="text-right">RM Quantity</th>
                <th width="15%" class="text-right">RM Value</th>
            </thead>
            <tbody>
                @php $total_material_value = 0; @endphp
                @if (!$formulation->materials->isEmpty())
                    @foreach ($formulation->materials as $key => $item)
                        <tr>
                            <td>{{ $item->material_code.' - '.$item->material_name }}</td>
                            <td class="text-center">{{ $item->category->name }}</td>
                            <td class="text-center">{{ $item->unit->unit_name }}</td>
                            <td class="text-right">{{ $item->cost }}</td>
                            <td class="text-right">{{ $item->pivot->qty }}</td>
                            <td class="text-right">{{ number_format(($item->cost * $item->pivot->qty),4,'.','') }}</td>
                        </tr>
                        @php $total_material_value += ($item->cost * $item->pivot->qty); @endphp
                    @endforeach
                    <tr class="bg-primary text-white">
                        <td colspan="5" class="text-right font-weight-bolder">Total</td>
                        <td class="text-right font-weight-bolder">{{ number_format($total_material_value,4,'.','') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endforeach
@else
    <div class="col-md-12 text-center"><h2 class="text-danger">No Data Found</h2></div>
@endif