<?php

namespace Modules\Production\Http\Requests;

use App\Http\Requests\FormRequest;

class ProductMixingRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];

    public function rules()
    {

        $this->rules['batch_no']             = ['required','unique:productions,batch_no'];
        $this->rules['mfg_date']             = ['required','date','date_format:Y-m-d'];
        $this->rules['exp_date']             = ['required','date','date_format:Y-m-d'];
        $this->rules['reference_no']         = ['required'];
        $this->rules['serial_no']            = ['required'];
        $this->rules['product_id']           = ['required'];
        $this->rules['formulation_no']       = ['required'];
        $this->rules['unit_id']              = ['required'];
        $this->rules['total_fg_qty']         = ['required'];
        $this->rules['total_cost']           = ['required','numeric','gt:0'];
        $this->rules['total_net_cost']       = ['required','numeric','gt:0'];
        $this->rules['pwarehouse_id']        = ['required'];
        $this->rules['mwarehouse_id']        = ['required'];

        $this->messages['pwarehouse_id.requried'] = 'The product warehouse field is required';
        $this->messages['mwarehouse_id.requried'] = 'The material warehouse field is required';
        if(permission('product-mixing-approve') && (!request()->has('update_id'))){
            $this->rules['status'] = ['required'];
            $this->messages['status.requried'] = 'The approve mixing field is required';
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
