<?php

namespace Modules\Transfer\Http\Requests;

use App\Http\Requests\FormRequest;

class TransferFormRequest extends FormRequest
{
    protected $rules;
    protected $messages;
    public function rules()
    {
        $this->rules['chalan_no']    = ['required','unique:transfers,chalan_no'];
        $this->rules['transfer_date']    = ['required','date','date_format:Y-m-d'];
        $this->rules['from_warehouse_id']    = ['required'];
        $this->rules['to_warehouse_id']    = ['required'];
        $this->rules['shipping_cost']   = ['nullable','numeric','gte:0'];

        if(request()->has('materials'))
        {
            foreach (request()->materials as $key => $value) {
                $this->rules   ['materials.'.$key.'.qty']          = ['required','numeric','gt:0','lte:'.$value['stock_qty']];
                $this->messages['materials.'.$key.'.qty.required'] = 'This field is required';
                $this->messages['materials.'.$key.'.qty.numeric']  = 'The value must be numeric';
                $this->messages['materials.'.$key.'.qty.gt']       = 'The value must be greater than 0';
                $this->messages['materials.'.$key.'.qty.lte']      = 'The value must be less than or equal to stock available quantity';
            }
        }

        if(!empty(request()->transfer_id))
        {
            $this->rules['chalan_no'][1] = 'unique:transfers,chalan_no,'.request()->transfer_id;
        }

        return $this->rules;
    }


    public function authorize()
    {
        return true;
    }
}
