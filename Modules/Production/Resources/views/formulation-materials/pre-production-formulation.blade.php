@if (!$formulation->materials->isEmpty())
@foreach ($formulation->materials as $key => $item)
    <tr>
        <td>
            <input type="text" class="form-control" value="{{ $item->material_code.' - '.$item->material_name }}" name="production[{{ $tab }}][materials][{{ $key+1 }}][material_name]" id="production_{{ $tab }}_materials_{{ $key+1 }}_material_name" data-id="{{ $key+1 }}" readonly>
            <input type="hidden" class="form-control" value="{{ $item->pivot->material_id }}" name="production[{{ $tab }}][materials][{{ $key+1 }}][material_id]" id="production_{{ $tab }}_materials_{{ $key+1 }}_material_id" data-id="{{ $key+1 }}" readonly>
        </td>
        <td>
            <input type="text" class="form-control text-center" value="{{ $item->category->name }}" name="production[{{ $tab }}][materials][{{ $key+1 }}][category]" id="production_{{ $tab }}_materials_{{ $key+1 }}_category" data-id="{{ $key+1 }}" readonly>
            <input type="hidden" class="form-control text-center prefix" value="+" name="production[{{ $tab }}][materials][{{ $key+1 }}][prefix]" id="production_{{ $tab }}_materials_{{ $key+1 }}_prefix" data-id="{{ $key+1 }}" readonly>
        </td>
        <td>
            <input type="hidden" class="form-control" value="{{ $item->pivot->unit_id }}" name="production[{{ $tab }}][materials][{{ $key+1 }}][unit_id]" id="production_{{ $tab }}_materials_{{ $key+1 }}_unit_id" data-id="{{ $key+1 }}">
            <input type="text" class="form-control text-center" value="{{ $item->unit->unit_name }}" name="production[{{ $tab }}][materials][{{ $key+1 }}][unit_name]" id="production_{{ $tab }}_materials_{{ $key+1 }}_unit_name" data-id="{{ $key+1 }}" readonly>
        </td>
        <td>
            <input type="text" class="form-control text-right" name="production[{{ $tab }}][materials][{{ $key+1 }}][rate]" value="{{ $item->cost }}" id="production_{{ $tab }}_materials_{{ $key+1 }}_rate" data-id="{{ $key+1 }}" readonly>
        </td>
        <td>
            <input type="text" class="form-control text-right qty" name="production[{{ $tab }}][materials][{{ $key+1 }}][qty]" value="{{ $item->pivot->qty }}" id="production_{{ $tab }}_materials_{{ $key+1 }}_qty" data-id="{{ $key+1 }}" readonly>
            <input type="hidden" class="form-control text-right per_unit_qty" name="production[{{ $tab }}][materials][{{ $key+1 }}][per_unit_qty]" value="{{ ($item->pivot->qty / $formulation->total_fg_qty) }}" id="production_{{ $tab }}_materials_{{ $key+1 }}_per_unit_qty" data-id="{{ $key+1 }}" readonly>
        </td>
        <td>
            <input type="text" class="form-control text-right total" name="production[{{ $tab }}][materials][{{ $key+1 }}][total]" value="{{ number_format(($item->cost * $item->pivot->qty),4,'.','') }}" id="production_{{ $tab }}_materials_{{ $key+1 }}_total" data-id="{{ $key+1 }}" readonly>
            <input type="hidden" class="form-control text-right per_unit_total" name="production[{{ $tab }}][materials][{{ $key+1 }}][per_unit_total]" value="{{ number_format((($item->cost * $item->pivot->qty) / $formulation->total_fg_qty),4,'.','') }}" id="production_{{ $tab }}_materials_{{ $key+1 }}_per_unit_total" data-id="{{ $key+1 }}" readonly>
        </td>
    </tr>
@endforeach
@endif