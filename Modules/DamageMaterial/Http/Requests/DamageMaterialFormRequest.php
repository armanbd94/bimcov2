<?php

namespace Modules\DamageMaterial\Http\Requests;

use App\Http\Requests\FormRequest;

class DamageMaterialFormRequest extends FormRequest
{

    public function rules()
    {
        return [
            'warehouse_id' => 'required',
            'material_id'  => 'required',
            'damage_date'  => 'required|date|date_format:Y-m-d',
            'unit_id' => 'required',
            'unit_name' => 'required',
            'qty' => 'required|numeric|gt:0|lte:'.request()->stock_qty,
            'stock_qty' => 'required|numeric|gt:0',
            'net_unit_cost' => 'required|numeric|gt:0',
            'total' => 'required|numeric|gt:0',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
