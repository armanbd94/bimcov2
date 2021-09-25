<?php

namespace Modules\Setting\Http\Requests;

use App\Http\Requests\FormRequest;

class LaborBillFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['name']  = ['required', 'string', 'unique:labor_bills,name'];

        if (request()->update_id) {
            $rules['name'] = 'unique:labor_bills,name,' . request()->update_id;
        }

        return $rules;
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
