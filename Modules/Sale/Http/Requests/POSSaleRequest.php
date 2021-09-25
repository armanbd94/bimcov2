<?php

namespace Modules\Sale\Http\Requests;

use App\Http\Requests\FormRequest;

class POSSaleRequest extends FormRequest
{
    protected $rules;
    protected $messages;
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(empty(request()->sale_id)){
            $this->rules['invoice_no']      = ['required','unique:sales,invoice_no'];
        }else{
            $this->rules['invoice_no']      = ['required','unique:sales,invoice_no,'.request()->sale_id];
        }
        $this->rules['sale_date']       = ['required','date','date_format:Y-m-d'];
        $this->rules['warehouse_id']    = ['required'];
        $this->rules['customer_name']     = ['required'];

        if(request()->has('products'))
        {
            foreach (request()->products as $key => $value) {
                $this->rules['products.'.$key.'.qty']             = ['required','numeric','gt:0','lte:'.$value['stock_qty']];
                $this->messages['products.'.$key.'.qty.required'] = 'This field is required';
                $this->messages['products.'.$key.'.qty.numeric']  = 'The value must be numeric';
                $this->messages['products.'.$key.'.qty.gt']       = 'The value must be greater than 0';
                $this->rules['products.'.$key.'.net_unit_price']             = ['required','numeric','gt:0'];
                $this->messages['products.'.$key.'.net_unit_price.required'] = 'This field is required';
                $this->messages['products.'.$key.'.net_unit_price.numeric']  = 'The value must be numeric';
                $this->messages['products.'.$key.'.net_unit_price.gt']       = 'The value must be greater than 0';
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
