<?php

namespace Modules\DamageMaterial\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Material\Entities\WarehouseMaterial;
use Modules\DamageMaterial\Entities\DamageMaterial;
use Modules\DamageMaterial\Http\Requests\DamageMaterialFormRequest;
use Modules\Material\Entities\Material;

class DamageMaterialController extends BaseController
{
    public function __construct(DamageMaterial $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('damage-material-access')){
            $this->setPageData('Material','Material','fas fa-toolbox',[['name' => 'Material']]);
            $data = [
                'warehouses' => Warehouse::activeWarehouses(),
            ];
            return view('damagematerial::index',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('damage-material-access')){


                if (!empty($request->from_date)) {
                    $this->model->setFromDate($request->from_date);
                }
                if (!empty($request->to_date)) {
                    $this->model->setToDate($request->to_date);
                }
                if (!empty($request->warehouse_id)) {
                    $this->model->setWarehouseID($request->warehouse_id);
                }
                if (!empty($request->material_id)) {
                    $this->model->setMaterialID($request->material_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('damage-material-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('damage-material-view')){
                        $action .= ' <a class="dropdown-item view_data" data-id="' . $value->id . '" data-name="' . $value->material_name . '">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('damage-material-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->material_name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    $row = [];
                    if(permission('damage-material-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->material_name;
                    $row[] = $value->material_code;
                    $row[] = $value->warehouse_name;
                    $row[] = $value->unit_name;
                    $row[] = $value->qty;
                    $row[] = number_format($value->net_unit_cost,4,'.','');
                    $row[] = number_format($value->total,4,'.','');
                    $row[] = date(config('settings.date_format'),strtotime($value->damage_date));
                    $row[] = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(DamageMaterialFormRequest $request)
    {
        if($request->ajax()){
            if(permission('damage-material-add') || permission('damage-material-edit')){
                DB::beginTransaction();
                try {
                    $collection = collect($request->validated())->only([
                        'warehouse_id','material_id','unit_id','qty','net_unit_cost','total','damage_date'
                    ]);
                    if(empty($request->update_id))
                    {
                        $collection = $collection->merge(['created_by'=>auth()->user()->id]);
                        $result = $this->model->create($collection->all());
                        if($result)
                        {
                            $material = Material::find($request->material_id);
                            if($material)
                            {
                                $material->qty -= $request->qty;
                                $material->update();
                            }
                            $warehouse_material = WarehouseMaterial::where(['warehouse_id'=>$request->warehouse_id,'material_id'=>$request->material_id])->first();
                            if($warehouse_material)
                            {
                                $warehouse_material->qty -= $request->qty;
                                $warehouse_material->update();
                            }
                        }
                    }else{
                        $damage_material = $this->model->find($request->update_id);
                        $restore_material = Material::find($damage_material->material_id);
                        if($restore_material)
                        {
                            $restore_material->qty += $damage_material->qty;
                            $restore_material->update();
                        }
                        $restore_warehouse_material = WarehouseMaterial::where(['warehouse_id'=>$damage_material->warehouse_id,'material_id'=>$damage_material->material_id])->first();
                        if($restore_warehouse_material)
                        {
                            $restore_warehouse_material->qty += $damage_material->qty;
                            $restore_warehouse_material->update();
                        }

                        $collection = $collection->merge(['modified_by'=>auth()->user()->id]);
                        $result = $damage_material->update($collection->all());
                        if($result)
                        {
                            $material = Material::find($request->material_id);
                            if($material)
                            {
                                $material->qty -= $request->qty;
                                $material->update();
                            }
                            $warehouse_material = WarehouseMaterial::where(['warehouse_id'=>$request->warehouse_id,'material_id'=>$request->material_id])->first();
                            if($warehouse_material)
                            {
                                $warehouse_material->qty -= $request->qty;
                                $warehouse_material->update();
                            }
                        }
                    }
                    $output     = $this->store_message($result, $request->update_id);
                    DB::commit();
                }catch (\Throwable $th) {
                   DB::rollback();
                   $output = ['status' => 'error','message' => $th->getMessage()];
                }
            }else{
                $output     = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function show(Request $request)
    {
        if($request->ajax()){
            if(permission('damage-material-view')){
                $material = $this->model->with('unit','warehouse','material')->findOrFail($request->id);
                return view('damagematerial::view-modal-data',compact('material'))->render();
            }
        }
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('damage-material-edit')){
                $data   = $this->model->with('unit')->findOrFail($request->id);
                $damage_material = [];
                if($data){
                    $damage_material = [
                        'id'           => $data->id,
                        'warehouse_id' => $data->warehouse_id,
                        'material_id' => $data->material_id,
                        'unit_id' => $data->unit_id,
                        'unit_name' => $data->unit->unit_name,
                        'qty' => $data->qty,
                        'net_unit_cost' => $data->net_unit_cost,
                        'total' => $data->total,
                        'date' => $data->damage_date,
                        'stock_qty' => DB::table('warehouse_material')->where(['warehouse_id'=>$data->warehouse_id,'material_id'=>$data->material_id])->value('qty')
                    ];
                }
                $output = $this->data_message($damage_material); //if data found then it will return data otherwise return error message
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('damage-material-delete')){
                $damage_material = $this->model->find($request->id);
                $restore_material = Material::find($damage_material->material_id);
                if($restore_material)
                {
                    $restore_material->qty += $damage_material->qty;
                    $restore_material->update();
                }
                $restore_warehouse_material = WarehouseMaterial::where(['warehouse_id'=>$damage_material->warehouse_id,'material_id'=>$damage_material->material_id])->first();
                if($restore_warehouse_material)
                {
                    $restore_warehouse_material->qty += $damage_material->qty;
                    $restore_warehouse_material->update();
                }
                $result = $damage_material->delete();
                $output   = $this->delete_message($result);
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('damage-material-bulk-delete')){
                foreach ($request->ids as $id) {
                    $damage_material = $this->model->find($id);
                    $restore_material = Material::find($damage_material->material_id);
                    if($restore_material)
                    {
                        $restore_material->qty += $damage_material->qty;
                        $restore_material->update();
                    }
                    $restore_warehouse_material = WarehouseMaterial::where(['warehouse_id'=>$damage_material->warehouse_id,'material_id'=>$damage_material->material_id])->first();
                    if($restore_warehouse_material)
                    {
                        $restore_warehouse_material->qty += $damage_material->qty;
                        $restore_warehouse_material->update();
                    }
                    $result = $damage_material->delete();
                }
                $output   = $this->bulk_delete_message($result);
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }



}
