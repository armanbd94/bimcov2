<?php

namespace Modules\Formulation\Http\Requests;

use App\Http\Requests\FormRequest;

class FormulationRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];

    public function rules()
    {
        if(empty(request()->update_id)){
            $this->rules['serial_no'] = ['required','numeric','unique:formulations,id'];
        }else{
            $this->rules['serial_no'] = ['required','numeric','unique:formulations,id,'.request()->update_id];
        }
        $this->rules['date']                 = ['required','date','date_format:Y-m-d'];
        $this->rules['product_id']           = ['required'];
        $this->rules['formulation_no']       = ['required'];
        $this->rules['unit_id']              = ['required'];
        $this->rules['total_fg_qty']         = ['required'];
        $this->rules['total_material_qty']   = ['required','numeric','gt:0'];
        $this->rules['total_material_value'] = ['required','numeric','gt:0'];
        $this->rules['note']                 = ['nullable','string'];


        $collection = collect(request());
        if($collection->has('materials')){
            
            foreach (request()->materials as $key => $value) {
                $this->rules ['materials.'.$key.'.material_id'] = ['required'];
                $this->rules ['materials.'.$key.'.unit_name']     = ['required'];
                $this->rules ['materials.'.$key.'.rate']        = ['required','numeric','gt:0'];
                $this->rules ['materials.'.$key.'.qty']         = ['required','numeric','gt:0'];
                $this->rules ['materials.'.$key.'.total']       = ['required','numeric','gt:0'];

                $this->messages['materials.'.$key.'.material_id.required'] = 'The field is required';
                $this->messages['materials.'.$key.'.unit_name.required']     = 'The field is required';
                $this->messages['materials.'.$key.'.rate.required']        = 'The field is required';
                $this->messages['materials.'.$key.'.qty.required']         = 'The field is required';
                $this->messages['materials.'.$key.'.total.required']       = 'The field is required';
                $this->messages['materials.'.$key.'.rate.numeric']         = 'The field value must be numeric';
                $this->messages['materials.'.$key.'.rate.gt']              = 'The field value must be greater than 0 ';
                $this->messages['materials.'.$key.'.qty.numeric']          = 'The field value must be numeric';
                $this->messages['materials.'.$key.'.qty.gt']               = 'The field value must be greater than 0 ';
                $this->messages['materials.'.$key.'.total.numeric']        = 'The field value must be numeric';
                $this->messages['materials.'.$key.'.total.gt']             = 'The field value must be greater than 0 ';

            }
        }
        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
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
