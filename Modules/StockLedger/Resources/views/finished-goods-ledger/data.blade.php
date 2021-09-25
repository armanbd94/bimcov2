@if ($ledger_data)
@foreach ($ledger_data as $value)
    <tr>
        <td class="text-center">{{ date('j-m-y',strtotime($value['date'])) }}</td>
        <td>{{ $value['name'] }}</td>
        <td class="text-center">{{ $value['unit_name'] }}</td>
        
        <td class="text-right">{{ $value['previous_qty'] }}</td>
        <td class="text-right">{{ $value['previous_cost'] }}</td>
        <td class="text-right">{{ $value['previous_value'] }}</td>
        
        <td class="text-center">
            <ul>
                @if (!empty($value['batch_numbers']))
                    @foreach ($value['batch_numbers'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                @endif
            </ul>
        </td>
        <td class="text-center">
            <ul>
                @if (!empty($value['return_numbers']))
                    @foreach ($value['return_numbers'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                @endif
            </ul>
        </td>
        <td class="text-right">{{ $value['production_qty'] }}</td>
        <td class="text-right">{{ $value['production_cost'] }}</td>
        <td class="text-right">{{ $value['production_value'] }}</td>

        <td class="text-center">
            <ul>
                @if (!empty($value['sold_numbers']))
                    @foreach ($value['sold_numbers'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                @endif
            </ul>
        </td>
        <td class="text-right">{{ $value['sold_qty'] }}</td>
        <td class="text-right">{{ $value['sold_cost'] }}</td>
        <td class="text-right">{{ $value['sold_value'] }}</td>
        
        <td class="text-right">{{ $value['current_qty'] }}</td>
        <td class="text-right">{{ $value['current_cost'] }}</td>
        <td class="text-right">{{ $value['current_value'] }}</td>
    </tr>
@endforeach
<tr class="bg-primary text-white">
    <td colspan="6"  class="text-right font-weight-bolder">Total</td>
    <td></td>
    <td></td>
    <td class="text-right font-weight-bolder">{{ $total_production_qty }}</td>
    
    <td></td>
    <td class="text-right font-weight-bolder">{{ number_format($total_production_value,4, '.', ',') }}</td>
    <td></td>
    <td class="text-right font-weight-bolder">{{ $total_sold_qty }}</td>
    <td></td>
    <td class="text-right font-weight-bolder">{{ number_format($total_sold_value,4, '.', ',') }}</td>
    <td class="text-right font-weight-bolder">{{ $total_current_qty }}</td>
    <td></td>
    <td class="text-right font-weight-bolder">{{ number_format($total_current_value,4, '.', ',') }}</td>
</tr>
@else   
<tr>
    <td colspan="16" class="text-center text-danger font-weight-bolder">No Data Found</td>
</tr>
    
@endif