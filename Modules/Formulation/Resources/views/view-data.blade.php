@php $total_material_value = 0; @endphp
@if (!$formulation->materials->isEmpty())
    @foreach ($formulation->materials as $key => $item)
        @php $total_material_value += number_format(($item->cost * $item->pivot->qty),4,'.',''); @endphp
    @endforeach
@endif
<table class="table table-bordered">
    <tr>
        <td><b>Date</b></td><td>{{ date('j-F-Y',strtotime($formulation->date)) }}</td>
        <td><b>Serial No.</b></td><td>{{ $formulation->id }}</td> 
        <td class="text-right"><b>Finish Goods</b></td><td class="text-right">{{ $formulation->product->name }}</td> 
        
    </tr>
    <tr>
        <td><b>FG Serial No.</b></td><td>{{ $formulation->formulation_no }}</td> 
        <td><b>Unit</b></td><td>{{ $formulation->unit->unit_name }}</td> 
        <td class="text-right"><b>Total FG Qty</b></td><td class="text-right">{{ $formulation->total_fg_qty }}</td> 
     </tr>
</table>

<table class="table table-bordered pb-5" id="material_table">
    <thead class="bg-primary">
        <th width="40%">Material</th>
        <th width="10%" class="text-center">Category</th>
        <th width="10%" class="text-center">Unit Name</th>
        <th width="10%" class="text-right">RM Rate</th>
        <th width="10%" class="text-center">RM Quantity</th>
        <th width="15%" class="text-right">RM Value</th>
    </thead>
    <tbody>
        @if (!$formulation->materials->isEmpty())
            @foreach ($formulation->materials as $key => $item)
                <tr>
                    <td>{{ $item->material_name.' - '.$item->material_code }}</td>
                    <td class="text-center">{{ $item->category->name }}</td>
                    <td class="text-center">{{ $item->unit->unit_name }}</td>
                    <td class="text-right">{{ $item->cost }}</td>
                    <td class="text-center">{{ $item->pivot->qty }}</td>
                    <td class="text-right">{{ number_format(($item->cost * $item->pivot->qty),4,'.','') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" class="text-right font-weight-bolder">Total</td>
                <td class="text-center font-weight-bolder">{{ $formulation->total_material_qty }}</td>
                <td class="text-right font-weight-bolder">{{ number_format($total_material_value,4,'.','') }}</td>
            </tr>
        @endif
    </tbody>
</table>