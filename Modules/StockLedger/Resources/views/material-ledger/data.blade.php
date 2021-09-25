@if ($ledger_data)
@foreach ($ledger_data as $value)
    <tr>
        <td class="text-center">{{ date('j-m-y',strtotime($value['date'])) }}</td>
        <td>{{ $value['material_name'] }}</td>
        <td class="text-center">{{ $value['unit_name'] }}</td>
        
        <td class="text-right font-weight-bolder" style="color:#002447 !important;">{{ $value['previous_qty'] }}</td>
        <td class="text-right font-weight-bolder" style="color:#002447 !important;">{{ $value['previous_cost'] }}</td>
        <td class="text-right font-weight-bolder" style="color:#002447 !important;">{{ $value['previous_value'] }}</td>
        
        <td class="text-center">
            <ul  style="list-style:none;margin:0;padding:0;">
                @if (!empty($value['purchase_numbers']))
                    @foreach ($value['purchase_numbers'] as $item)
                        <li class="text-center px-2 font-weight-bolder">{{ $item }}</li>
                    @endforeach
                @endif
            </ul>
        </td>
        <td class="text-right font-weight-bolder" style="color:#1F9F04 !important;">{{ $value['purchase_qty'] }}</td>
        <td class="text-right font-weight-bolder" style="color:#1F9F04 !important;">{{ $value['purchase_cost'] }}</td>
        <td class="text-right font-weight-bolder" style="color:#1F9F04 !important;">{{ $value['purchase_value'] }}</td>
        
        <td class="text-center">
            <ul  style="list-style:none;margin:0;padding:0;">
                @if (!empty($value['batch_numbers']))
                    @foreach ($value['batch_numbers'] as $item)
                        <li class="text-center px-2 font-weight-bolder">{{ $item }}</li>
                    @endforeach
                @endif
            </ul>
        </td>
        <td class="text-center">
            <ul  style="list-style:none;margin:0;padding:0;">
                @if (!empty($value['return_numbers']))
                    @foreach ($value['return_numbers'] as $item)
                        <li class="text-center px-2 font-weight-bolder">{{ $item }}</li>
                    @endforeach
                @endif
            </ul>
        </td>
        <td class="text-center">
            <ul style="list-style:none;margin:0;padding:0;">
                @if (!empty($value['damage_numbers']))
                    @foreach ($value['damage_numbers'] as $item)
                        <li class="text-center px-2 font-weight-bolder">{{ $item }}</li>
                    @endforeach
                @endif
            </ul>
        </td>
        <td class="text-right">
            <ul style="list-style:none;margin:0;padding:0;">
                <li class="text-center px-2 font-weight-bolder" style="color: darkred;">{{ $value['production_qty']['production_material_qty'] }}</li>
                <li class="text-center px-2 font-weight-bolder" style="color: darkred;">{{ $value['production_qty']['returned_material_qty'] }}</li>
                <li class="text-center px-2 font-weight-bolder" style="color: darkred;">{{ $value['production_qty']['damage_material_qty'] }}</li>
            </ul>
        </td>
        <td class="text-right">
            <ul style="list-style:none;margin:0;padding:0;">
                <li class="text-right px-2 font-weight-bolder" style="color: darkred;">{{ $value['production_cost']['production_material_cost'] }}</li>
                <li class="text-right px-2 font-weight-bolder" style="color: darkred;">{{ $value['production_cost']['returned_material_cost'] }}</li>
                <li class="text-right px-2 font-weight-bolder" style="color: darkred;">{{ $value['production_cost']['damage_material_cost'] }}</li>
            </ul>
        </td>
        <td class="text-right">
            <ul style="list-style:none;margin:0;padding:0;">
                <li class="text-right px-2 font-weight-bolder" style="color: darkred;">{{ $value['production_subtotal']['production_material_cost'] }}</li>
                <li class="text-right px-2 font-weight-bolder" style="color: darkred;">{{ $value['production_subtotal']['returned_material_cost'] }}</li>
                <li class="text-right px-2 font-weight-bolder" style="color: darkred;">{{ $value['production_subtotal']['damage_material_cost'] }}</li>
            </ul>
        </td>
        <td class="text-right font-weight-bolder" style="color: darkred;">{{ $value['production_value'] }}</td>
        <td class="text-right font-weight-bolder">{{ $value['current_qty'] }}</td>
        <td class="text-right font-weight-bolder">{{ $value['current_cost'] }}</td>
        <td class="text-right font-weight-bolder">{{ $value['current_value'] }}</td>
    </tr>
@endforeach
<tr class="bg-primary text-white">
    <td colspan="7"  class="text-right font-weight-bolder">Total</td>
    <td class="text-right font-weight-bolder">{{ $total_purchase_qty }}</td>
    <td></td>
    <td class="text-right font-weight-bolder">{{ number_format($total_purchase_value,4, '.', ',') }}</td>
    <td></td>
    <td></td>
    <td></td>
    <td class="text-right font-weight-bolder">{{ $total_production_qty }}</td>
    <td></td>
    <td></td>
    <td class="text-right font-weight-bolder">{{ number_format($total_production_value,4, '.', ',') }}</td>
    <td class="text-right font-weight-bolder">{{ $total_current_qty }}</td>
    <td></td>
    <td class="text-right font-weight-bolder">{{ number_format($total_current_value,4, '.', ',') }}</td>
</tr>
@else   
<tr>
    <td colspan="18" class="text-center text-danger font-weight-bolder">No Data Found</td>
</tr>
    
@endif