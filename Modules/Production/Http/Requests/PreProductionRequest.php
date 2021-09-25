<?php

namespace Modules\Production\Http\Requests;

use App\Http\Requests\FormRequest;

class PreProductionRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];

    public function rules()
    {

        // $this->rules['serial_no'] = ['required'];
        // $this->rules['product_id']           = ['required'];
        // $this->rules['formulation_no']       = ['required'];
        // $this->rules['unit_id']              = ['required'];
        // $this->rules['total_fg_qty']         = ['required'];
        // $this->rules['total_cost'] = ['required','numeric','gt:0'];
        // $this->rules['total_net_cost'] = ['required','numeric','gt:0'];
        // $this->rules['warehouse_id'] = ['required'];


        $collection = collect(request());
        if($collection->has('production')){
            
            foreach (request()->production as $key => $value) {
                
                $this->rules['production.'.$key.'.serial_no']      = ['required'];
                $this->rules['production.'.$key.'.product_id']     = ['required'];
                $this->rules['production.'.$key.'.formulation_no'] = ['required'];
                $this->rules['production.'.$key.'.unit_id']        = ['required'];
                $this->rules['production.'.$key.'.total_fg_qty']   = ['required','numeric','gt:0'];
                $this->rules['production.'.$key.'.total_cost']     = ['required','numeric','gt:0'];
                $this->rules['production.'.$key.'.total_net_cost'] = ['required','numeric','gt:0'];
                $this->rules['production.'.$key.'.mwarehouse_id']   = ['required'];

                $this->messages['production.'.$key.'.serial_no.required']      = 'The serial no field is required';
                $this->messages['production.'.$key.'.product_id.required']     = 'The finish goods field is required';
                $this->messages['production.'.$key.'.formulation_no.required'] = 'The formulation no field is required';
                $this->messages['production.'.$key.'.unit_id.required']        = 'The unit field is required';
                $this->messages['production.'.$key.'.total_fg_qty.required']   = 'The FG quantity field is required';
                $this->messages['production.'.$key.'.total_fg_qty.numeric']    = 'The FG quantity field value must be numeric';
                $this->messages['production.'.$key.'.total_fg_qty.gt']         = 'The FG quantity field value must be greater than 0 ';
                $this->messages['production.'.$key.'.total_cost.required']     = 'The total cost field is required';
                $this->messages['production.'.$key.'.total_cost.numeric']      = 'The total cost field value must be numeric';
                $this->messages['production.'.$key.'.total_cost.gt']           = 'The total cost field value must be greater than 0 ';
                $this->messages['production.'.$key.'.total_net_cost.required'] = 'The total net cost field is required';
                $this->messages['production.'.$key.'.total_net_cost.numeric']  = 'The total net cost field value must be numeric';
                $this->messages['production.'.$key.'.total_net_cost.gt']       = 'The total net cost field value must be greater than 0 ';
                $this->messages['production.'.$key.'.mwarehouse_id.required']   = 'The material warehouse field is required';

                // $this->rules ['materials.'.$key.'.material_id'] = ['required'];
                // $this->rules ['materials.'.$key.'.unit_name']     = ['required'];
                // $this->rules ['materials.'.$key.'.rate']        = ['required','numeric','gt:0'];
                // $this->rules ['materials.'.$key.'.qty']         = ['required','numeric','gt:0'];
                // $this->rules ['materials.'.$key.'.total']       = ['required','numeric','gt:0'];

                // $this->messages['materials.'.$key.'.material_id.required'] = 'The field is required';
                // $this->messages['materials.'.$key.'.unit_name.required']     = 'The field is required';
                // $this->messages['materials.'.$key.'.rate.required']        = 'The field is required';
                // $this->messages['materials.'.$key.'.qty.required']         = 'The field is required';
                // $this->messages['materials.'.$key.'.total.required']       = 'The field is required';
                // $this->messages['materials.'.$key.'.rate.numeric']         = 'The field value must be numeric';
                // $this->messages['materials.'.$key.'.rate.gt']              = 'The field value must be greater than 0 ';
                // $this->messages['materials.'.$key.'.qty.numeric']          = 'The field value must be numeric';
                // $this->messages['materials.'.$key.'.qty.gt']               = 'The field value must be greater than 0 ';
                // $this->messages['materials.'.$key.'.total.numeric']        = 'The field value must be numeric';
                // $this->messages['materials.'.$key.'.total.gt']             = 'The field value must be greater than 0 ';

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
