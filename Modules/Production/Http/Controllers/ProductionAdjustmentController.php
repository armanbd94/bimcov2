<?php

namespace Modules\Production\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Production\Entities\Production;
use Modules\Formulation\Entities\Formulation;
use Modules\Material\Entities\WarehouseMaterial;
use Modules\Production\Http\Requests\ProductionRequest;

class ProductionAdjustmentController extends BaseController
{
    public function __construct(Production $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('production-adjustment-access')){
            $this->setPageData('Production Adjustment','Production Adjustment','fas fa-sliders-h',[['name' => 'Production Adjustment']]);
            return view('production::production-adjustment.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function show(Request $request)
    {
        if(permission('production-adjustment-access')){
            $production = $this->model->with('materials','unit')->where('batch_no',$request->get('batch_no'))->first();
            if($production){
                if($production->production_status != 4){
                    $this->setPageData('Adjust '.$request->get("batch_no").' No. Production','Adjust '.$request->get("batch_no").' No. Production','fas fa-retweet',[['name' => 'Adjust Production']]);
                    $data = [
                        'materials'  => Material::with('unit:id,unit_name','category:id,name')->where('status',1)->get(),
                        'products'   => Product::with('unit:id,unit_name')->where('status',1)->get(),
                        'warehouses' => Warehouse::activeWarehouses(),
                        'production' => $production,
                        'formulations' => Formulation::with('unit')->where('product_id',$production->product_id)->get()
                    ];
                    return view('production::production-adjustment.view',$data);
                }else{
                    return redirect('production-adjustment')->with(['status'=>'error','message'=>'This Production is Complete. Complete Production cannot modified.']);
                }
            }else{
                return redirect('production-adjustment')->with(['status'=>'error','message'=>'No Production Data Found']);
            }
            
        }else{
            return $this->access_blocked();
        }
    }

    public function update(ProductionRequest $request)
    {
        if($request->ajax())
        {
            if(permission('production-adjustment-access'))
            {
                DB::beginTransaction();
                try {
                    $production = $this->model->with('materials')->find($request->update_id);
                    $production_data = [
                        'batch_no'          => $request->batch_no,
                        'mfg_date'          => $request->mfg_date,
                        'exp_date'          => $request->exp_date,
                        'date'              => $request->date,
                        'product_id'        => $request->product_id,
                        'formulation_id'    => $request->serial_no,
                        'unit_id'           => $request->unit_id,
                        'total_fg_qty'      => $request->total_fg_qty,
                        'per_unit_rm_qty'   => $request->total_material_qty,
                        'per_unit_rm_value' => $request->total_material_value,
                        'total_rm_qty'      => $request->total_rm_qty,
                        'total_cost'        => $request->total_cost,
                        'extra_cost'        => $request->extra_cost ? $request->extra_cost : 0,
                        'total_net_cost'    => $request->total_net_cost,
                        'per_unit_cost'     => $request->per_unit_cost,
                        'pwarehouse_id'     => $request->pwarehouse_id,
                        'mwarehouse_id'     => $request->mwarehouse_id,
                        'modified_by'       => auth()->user()->name,
                        'updated_at'        => date('Y-m-d H:i:s')
                    ];
                    
                    if($production->status == 1)
                    {
                        if(!$production->materials->isEmpty())
                        {
                            foreach ($production->materials as $material) {
    
                                $warehouse_material = WarehouseMaterial::where([
                                    ['warehouse_id',$production->mwarehouse_id],
                                    ['material_id',$material->id]
                                    ])->first();
                                if($warehouse_material){
                                    $warehouse_material->qty += $material->pivot->total_qty;
                                    $warehouse_material->update();
                                }

                                //Remove qty from material
                                $material_data = Material::find($material->id);
                                if($material_data){
                                    $material_data->qty += $material->pivot->total_qty;
                                    $material_data->update();
                                }
                            }
                        }


                    }

                    $production_materials = [];
                    if($request->has('materials')){
                        foreach($request->materials as $value)
                        {
                            $total_qty = $value['qty'] * $request->total_fg_qty;
                            $production_materials[$value['material_id']] = [
                                'unit_id'   => $value['unit_id'],
                                'qty'       => $value['qty'],//per unit material qty
                                'total_qty' => $total_qty, //per material total required qty
                                'rate'      => $value['rate'],
                                'total'     => $value['total'],
                            ];

                            if($production->status == 1){
                                $material = Material::where([['id',$value['material_id']],['qty','>',0]])->first();
                                if($material){
                                    $material->qty -= $total_qty;
                                    $material->update();
                                }
                                $warehosue_material = WarehouseMaterial::where([['warehouse_id',$request->mwarehouse_id],['material_id',$value['material_id']],['qty','>',0]])->first();
                                if($warehosue_material){
                                    $warehosue_material->qty -= $total_qty;
                                    $warehosue_material->update();
                                }
                            }
                        }
                        $production->materials()->sync($production_materials);
                    }
                    $result = $production->update($production_data);
                    $output  = $this->store_message($result, $request->update_id);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $th->getMessage()];
                }return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }
    }

}
