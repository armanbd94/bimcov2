<?php

namespace Modules\Transfer\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class TransferMaterialController extends Controller
{
    public function material_autocomplete_search(Request $request)
    {
        if($request->ajax())
        {
            if(!empty($request->search))
            {
                $output = [];
                $search_text = $request->search;
                $data =  DB::table('warehouse_material as wm')
                ->join('materials as m','wm.material_id','=','m.id')
                ->selectRaw('wm.warehouse_id,wm.material_id,m.id,m.material_name,m.material_code')
                ->where([['wm.warehouse_id',$request->warehouse_id],['wm.qty','>',0]])
                ->where(function ($query) use ($search_text){
                    $query->where('m.material_name','like','%'.$search_text.'%')
                    ->orWhere('m.material_code','like','%'.$search_text.'%');
                })
                ->get();
                
                // WarehouseMaterial::with('material')->where([
                //     [ 'warehouse_id',$request->warehouse_id],['qty','>',0]
                // ])->where(function($subQuery) use ($search_text)
                // {   
                //     $subQuery->whereHas('material', function ($query) use ($search_text){
                //         $query->where('name','like','%'.$search_text.'%')
                //         ->orWhere('code','like','%'.$search_text.'%');
                //     });
                // })->get();
                

                if(!$data->isEmpty())
                {
                    foreach ($data as $value) {
                        $item['id'] = $value->id;
                        $item['code'] = $value->material_code;
                        $item['value'] = $value->material_code.' - '.$value->material_name;
                        $item['label'] = $value->material_code.' - '.$value->material_name;
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

    public function material_search(Request $request)
    {
        if($request->ajax())
        {
            $material_data = DB::table('warehouse_material as wm')
            ->join('materials as m','wm.material_id','=','m.id')
            ->selectRaw('wm.warehouse_id,wm.material_id,wm.qty,m.id,m.material_name,m.cost,m.material_code,m.unit_id')
            ->where([['wm.warehouse_id',$request->warehouse_id],['wm.qty','>',0],['m.material_code',$request->data]])
            ->first();
            if($material_data)
            {
                $material['id']         = $material_data->id;
                $material['name']       = $material_data->material_name;
                $material['code']       = $material_data->material_code;
                $material['cost']      = $material_data->cost ?? 0;
                $material['qty']        = $material_data->qty;
                
                $units = Unit::where('base_unit',$material_data->unit_id)->orWhere('id',$material_data->unit_id)->get();
                $unit_name            = [];
                $unit_operator        = [];
                $unit_operation_value = [];
                if($units)
                {
                    foreach ($units as $unit) {
                        if($material_data->unit_id == $unit->id)
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
                $material['unit_name'] = implode(',',$unit_name).',';
                $material['unit_operator'] = implode(',',$unit_operator).',';
                $material['unit_operation_value'] = implode(',',$unit_operation_value).',';
                return $material;
            }
        }
    }
}
