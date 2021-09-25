<?php

namespace Modules\Sale\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\WarehouseProduct;

class ProductController extends Controller
{
    public function product_autocomplete_search(Request $request)
    {
        if($request->ajax())
        {
            if(!empty($request->search))
            {
                $output = [];
                $search_text = $request->search;
                $data =  WarehouseProduct::with('product')->where([
                    [ 'warehouse_id',$request->warehouse_id],['qty','>',0]
                ])->where(function($subQuery) use ($search_text)
                {   
                    $subQuery->whereHas('product', function ($query) use ($search_text){
                        $query->where('name','like','%'.$search_text.'%')
                        ->orWhere('code','like','%'.$search_text.'%');
                    });
                })->get();
                

                if(!$data->isEmpty())
                {
                    foreach ($data as $value) {
                        $item['id'] = $value->product->id;
                        $item['value'] = $value->product->code;
                        $item['label'] = $value->product->code.' - '.$value->product->name;
                        $output[] = $item;
                    }
                }else{
                    $output['value'] = '';
                    $output['label'] = 'No Record Found';
                }
                return $output;
            }
        }
    }

    public function product_search(Request $request)
    {
        if($request->ajax())
        {
            $product = Product::with('tax')->where('code',$request['data'])->first();
            if($product)
            {
                $product['id']         = $product->id;
                $product['name']       = $product->name;
                $product['code']       = $product->code;
                $product['price']      = $product->price;
                
                
                $product['tax_rate']   = $product->tax->rate;
                $product['tax_name']   = $product->tax->name;
                $product['tax_method'] = $product->tax_method;

                $warehouse_product = WarehouseProduct::where([
                    'warehouse_id'=> $request->warehouse_id,'product_id'=>$product->id])->first();
                $product['qty'] = $warehouse_product ? $warehouse_product->qty : 0;
                

    
                $units = Unit::where('base_unit',$product->unit_id)->orWhere('id',$product->unit_id)->get();
                $unit_name            = [];
                $unit_operator        = [];
                $unit_operation_value = [];
                if($units)
                {
                    foreach ($units as $unit) {
                        if($product->sale_unit_id == $unit->id)
                        {
                            array_unshift($unit_name,$unit->unit_name);
                            array_unshift($unit_operator,$unit->operator);
                            array_unshift($unit_operation_value,$unit->operation_value);
                        }else{
                            $unit_name           [] = $unit->unit_name;
                            $unit_operator       [] = $unit->operator;
                            $unit_operation_value[] = $unit->operation_value;
                        }
                    }
                }
                $product['unit_name'] = implode(',',$unit_name).',';
                $product['unit_operator'] = implode(',',$unit_operator).',';
                $product['unit_operation_value'] = implode(',',$unit_operation_value).',';
                return $product;
            }
        }
    }
}
