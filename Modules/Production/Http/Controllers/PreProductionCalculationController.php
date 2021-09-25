<?php

namespace Modules\Production\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Production\Entities\Production;
use Modules\Formulation\Entities\Formulation;
use Modules\Production\Entities\PreProduction;
use Modules\Material\Entities\WarehouseMaterial;
use Modules\Production\Http\Requests\PreProductionRequest;
use Modules\Production\Http\Requests\ProductMixingRequest;
use Modules\Production\Http\Requests\PreProductionUpdateRequest;

class PreProductionCalculationController extends BaseController
{
    public function __construct(PreProduction $model)
    {
        $this->model = $model;
    }

   public function index()
   {
        if(permission('pre-production-access')){
            $this->setPageData('Pre Production List','Pre Production List','fas fa-calculator',[['name' => 'Pre Production List']]);
            $data = [
                'products'    => Product::with('unit:id,unit_name')->where('status',1)->get(),
            ];
            return view('production::pre-production-calculation.index',$data);
        }else{
            return $this->access_blocked(); 
        }
   }

   public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('product-mixing-access')) {
                if (!empty($request->serial_no)) {
                    $this->model->setSerialNo($request->serial_no);
                }
                if (!empty($request->product_id)) {
                    $this->model->setProductID($request->product_id);
                }
                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }
                if (!empty($request->status)) {
                    $this->model->setStatus($request->status);
                }
                if (!empty($request->production_status)) {
                    $this->model->setProductionStatus($request->production_status);
                }

                $this->set_datatable_default_properties($request); //set datatable default properties
                $list = $this->model->getDatatableList(); //get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if (permission('pre-production-edit')) {
                        if ($value->status != 1) {
                            $action .= ' <a class="dropdown-item" href="' . route("pre.production.edit", $value->id) . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                        }
                    }

                    if (permission('pre-production-status-change')) {
                        if ($value->status != 1) {
                            $action .= ' <a class="dropdown-item change_status"  data-id="' . $value->id . '" data-name="' . $value->serial_no . '" data-status="' . $value->status . '"><i class="fas fa-check-circle text-success mr-2"></i> Convert to Production</a>';
                        }
                    }

                    if (permission('pre-production-view')) {
                        $action .= ' <a class="dropdown-item"  href="' . url("pre-production/view", $value->id) . '">' . self::ACTION_BUTTON['View'] . '</a>';
                    }

                    if (permission('pre-production-delete')) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->serial_no . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                    }

                    $row = [];
                    if (permission('pre-production-bulk-delete')) {
                        $row[] = row_checkbox($value->id); //custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    if (permission('pre-production-view')) {
                        $row[] = '<a class="dropdown-item" style="text-decoration:underline;" href="' . url("pre-production/view", $value->id) . '">' . $value->serial_no . '</a>';
                    } else {
                        $row[] = $value->serial_no;
                    }
                    $row[] = date(config('settings.date_format'), strtotime($value->date));
                    $row[] = $value->product->code . ' - ' . $value->product->name;
                    $row[] = $value->formulation_id;
                    $row[] = $value->formulation->formulation_no;
                    $row[] = $value->unit->unit_name;
                    $row[] = $value->total_fg_qty;
                    $row[] = number_format($value->total_cost, 4, '.', '');
                    $row[] = number_format($value->extra_cost, 4, '.', '');
                    $row[] = number_format($value->total_net_cost, 4, '.', '');
                    $row[] = number_format($value->per_unit_cost, 4, '.', '');
                    $row[] = $value->status == 1 ? '<span class="badge badge-success">Converted</span>' : 'N/A';
                    $row[] = action_button($action); //custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                    $this->model->count_filtered(), $data);
            }
        } else {
            return response()->json($this->unauthorized());
        }
    }

   public function create()
   {
        if(permission('pre-production-add')){
            $this->setPageData('Pre Production Calculation','Pre Production Calculation','fas fa-calculator',[['name' => 'Pre Production Calculation']]);
            $data = [
                'materials'  => Material::with('unit:id,unit_name', 'category:id,name')->where('status', 1)->get(),
                'products'   => Product::with('unit:id,unit_name')->where('status', 1)->get(),
                'warehouses' => Warehouse::activeWarehouses(),
            ];
            return view('production::pre-production-calculation.create',$data);
        }else{
            return $this->access_blocked(); 
        }
   }

   public function store(PreProductionRequest $request)
    {
        if ($request->ajax()) {
            // dd($request->all());
            if (permission('pre-production-add')) {
                DB::beginTransaction();
                try {
                    if($request->has('production')){
                        foreach($request->production as $production)
                        {
                            $last_serial_no = $this->model->whereDate('created_at', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'))->whereDate('created_at', '<=', Carbon::now()->endOfMonth()->format('Y-m-d'))->count();
                            $serial_no = $production['product_id'] . ($last_serial_no ? sprintf("%02d", ($last_serial_no + 1)) : sprintf("%02d", 1)) . date('my');
                            $production_data = [
                                'serial_no'         => $serial_no,
                                'date'              => $production['date'],
                                'product_id'        => $production['product_id'],
                                'formulation_id'    => $production['serial_no'],
                                'unit_id'           => $production['unit_id'],
                                'total_fg_qty'      => $production['total_fg_qty'],
                                'total_cost'        => $production['total_cost'],
                                'extra_cost'        => $production['extra_cost'] ? $production['extra_cost'] : 0,
                                'total_net_cost'    => $production['total_net_cost'],
                                'per_unit_cost'     => $production['per_unit_cost'],
                                'mwarehouse_id'     => $production['mwarehouse_id'],
                                'created_by'        => auth()->user()->id,
                                'created_at'        => date('Y-m-d H:i:s'),
                            ];
                            
                            $result = $this->model->create($production_data);
                            $pre_production = $this->model->with('materials', 'packaging_materials')->find($result->id);
                            //Formulation Materials
                            $production_materials = [];
                            if (!empty($production['materials']) && count($production['materials']) > 0) {
                                
                                foreach ($production['materials'] as $value) {
                                    $production_materials[$value['material_id']] = [
                                        'unit_id' => $value['unit_id'],
                                        'qty'     => $value['qty'],
                                        'rate'    => $value['rate'],
                                        'total'   => $value['total'],
                                    ];
                                }
                                $pre_production->materials()->sync($production_materials);
                            }
                            //Extra Materials Addition or Subtraction
                            $production_packaging_materials = [];
                            if (!empty($production['packaging_materials']) && count($production['packaging_materials']) > 0) {
                                foreach ($production['packaging_materials'] as $value) {
                                    if(!empty($value['qty'])){
                                        $production_packaging_materials[$value['material_id']] = [
                                            'prefix'  => $value['prefix'],
                                            'unit_id' => $value['unit_id'],
                                            'qty'     => $value['qty'],
                                            'rate'    => $value['rate'],
                                            'total'   => $value['total'],
                                        ];
                                    }
                                }
                                $pre_production->packaging_materials()->sync($production_packaging_materials);
                            }
                        }
                    }

                    $output = $this->store_message($result, null);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollback();
                    $output = ['status' => 'error', 'message' => $th->getMessage()];
                }return response()->json($output);
            } else {
                return response()->json($this->unauthorized());
            }
        }
    }

    public function show(int $id)
    {
        if (permission('pre-production-view')) {
            $this->setPageData('Pre Production Details', 'Pre Production Details', 'fas fa-eye', [['name' => 'Pre Production Details']]);
            $data = [
                'production' => $this->model->with('materials', 'unit', 'product', 'formulation','packaging_materials')->findOrFail($id),
            ];
            return view('production::pre-production-calculation.details', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function edit(int $id)
    {
        if (permission('pre-production-edit')) {
            $this->setPageData('Pre Production Edit', 'Pre Production Edit', 'fas fa-edit', [['name' => 'Pre Production Edit']]);
            $production = $this->model->with('materials', 'unit','packaging_materials')->findOrFail($id);
            // dd($production->materials[0]);
            if ($production->production_status != 4 && $production->status != 1) {
                $data = [
                    'materials'    => Material::with('unit:id,unit_name', 'category:id,name')->where('status', 1)->get(),
                    'products'     => Product::with('unit:id,unit_name')->where('status', 1)->get(),
                    'warehouses'   => Warehouse::activeWarehouses(),
                    'production'   => $production,
                    'formulations' => Formulation::with('unit')->where('product_id', $production->product_id)->get(),
                ];
                return view('production::pre-production-calculation.edit', $data);
            } else {
                return $this->access_blocked();
            }
        } else {
            return $this->access_blocked();
        }
    }

    public function update(PreProductionUpdateRequest $request)
    {
        if ($request->ajax()) {
            if (permission('product-mixing-edit')) {
                DB::beginTransaction();
                try {
                    $production = $this->model->with('materials', 'packaging_materials')->find($request->update_id);
                    $production_data = [
                        'date'           => $request->date,
                        'product_id'     => $request->product_id,
                        'formulation_id' => $request->serial_no,
                        'unit_id'        => $request->unit_id,
                        'total_fg_qty'   => $request->total_fg_qty,
                        'total_cost'     => $request->total_cost,
                        'extra_cost'     => $request->extra_cost ? $request->extra_cost : 0,
                        'total_net_cost' => $request->total_net_cost,
                        'per_unit_cost'  => $request->per_unit_cost,
                        'mwarehouse_id'  => $request->mwarehouse_id,
                        'modified_by'    => auth()->user()->id,
                        'updated_at'     => date('Y-m-d H:i:s'),
                    ];

                    $production_materials = [];
                    if ($request->has('materials')) {
                        foreach ($request->materials as $value) {
                            $production_materials[$value['material_id']] = [
                                'unit_id' => $value['unit_id'],
                                'qty'     => $value['qty'],
                                'rate'    => $value['rate'],
                                'total'   => $value['total'],
                            ];
                        }
                        $production->materials()->sync($production_materials);
                    }

                    $production_packaging_materials = [];
                    if ($request->has('packaging_materials')) {
                        foreach ($request->packaging_materials as $value) {
                            if(!empty($value['qty'])){
                                $production_packaging_materials[$value['material_id']] = [
                                    'prefix'  => $value['prefix'],
                                    'unit_id' => $value['unit_id'],
                                    'qty'     => $value['qty'],
                                    'rate'    => $value['rate'],
                                    'total'   => $value['total'],
                                ];
                            }
                        }
                        $production->packaging_materials()->sync($production_packaging_materials);
                    }
                    $result = $production->update($production_data);
                    $output = $this->store_message($result, $request->update_id);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollback();
                    $output = ['status' => 'error', 'message' => $th->getMessage()];
                }return response()->json($output);
            } else {
                return response()->json($this->unauthorized());
            }
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax()) {
            if (permission('product-mixing-delete')) {
                DB::beginTransaction();
                try {
                    $productionData = $this->model->with('materials', 'packaging_materials')->find($request->id);
                    
                    //Formulation Materials
                    if (!$productionData->materials->isEmpty()) {
                        $productionData->materials()->detach();
                    }

                    //Extra Materials Addition or Subtraction
                    if (!$productionData->packaging_materials->isEmpty()) {
                        $productionData->packaging_materials()->detach();
                    }
                    $result = $productionData->delete();
                    $output = $this->delete_message($result);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollback();
                    $output = ['status' => 'error', 'message' => $th->getMessage()];
                }
                return response()->json($output);
            } else {
                return response()->json($this->unauthorized());
            }
        }
    }

    public function bulk_delete(Request $request)
    {
        if ($request->ajax()) {
            if (permission('product-mixing-bulk-delete')) {
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $productionData = $this->model->with('materials', 'packaging_materials')->find($id);
                        //Formulation Materials
                        if (!$productionData->materials->isEmpty()) {
                            $productionData->materials()->detach();
                        }

                        //Extra Materials Addition or Subtraction
                        if (!$productionData->packaging_materials->isEmpty()) {
                            $productionData->packaging_materials()->detach();
                        }
                        $result = $productionData->delete();
                    }
                    $output = $result ? ['status' => 'success', 'message' => 'Data has been deleted successfully'] : ['status' => 'error', 'message' => 'failed to delete data'];
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error', 'message' => $e->getMessage()];
                }
            } else {
                $output = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

   public function check_material_stock(PreProductionRequest $request)
   {
        if($request->ajax())
        {
            // dd($request->production[$request->tab]);
            $formulation_materials = [];
            $below_qty = 0;
            if($request->has('production')){
                foreach($request->production[$request->tab]['materials'] as $value)
                {
                    $material = Material::find($value['material_id']);
                    $material_stock = WarehouseMaterial::where([['material_id',$value['material_id']],['warehouse_id',$request->production[$request->tab]['mwarehouse_id']]])->first();
                    $stock_qty = 0;
                    $background = '';
                    if($material_stock){
                        $stock_qty = $material_stock->qty;
                        if($material_stock->qty < $value['qty']){
                            $background = 'bg-danger';
                            $below_qty++;
                        }
                    }else{
                        $background = 'bg-danger';
                        $below_qty++;
                    }
                    $formulation_materials[] = [
                        'material_id'   => $material ? $material->id : '',
                        'material_name' => $material ? $material->material_code.' - '.$material->material_name : '',
                        'category'      => $value['category'],
                        'unit_id'       => $value['unit_id'],
                        'unit_name'     => $value['unit_name'],
                        'stock_qty'     => $stock_qty,
                        'qty'           => $value['qty'],
                        'rate'          => $value['rate'],
                        'total'         => $value['total'],
                        'background'    => $background,
                    ];
                }
            }
            
            $data = [
                'materials'    =>  $formulation_materials,
                'below_qty'    => $below_qty
            ];
            // dd($data['materials'][0]['material_name']);
            return view('production::pre-production-calculation.view-data',$data)->render();
        }
   }

   public function pre_production_to_product_mixing(int $id)
   {
        if (permission('pre-production-status-change')) {
            $this->setPageData('Product Mixing Add', 'Product Mixing Add', 'fas fa-plus-square', [['name' => 'Product Mixing Add']]);
            $production = $this->model->with('product','formulation','materials', 'unit','packaging_materials')->findOrFail($id);
            $serial_no = Production::whereDate('created_at', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'))->whereDate('created_at', '<=', Carbon::now()->endOfMonth()->format('Y-m-d'))->count();
            $batch_no = $production->product_id . ($serial_no ? sprintf("%02d", ($serial_no + 1)) : sprintf("%02d", 1)) . date('my');
            $data = [
                'materials'    => Material::with('unit:id,unit_name', 'category:id,name')->where('status', 1)->get(),
                'warehouses'   => Warehouse::activeWarehouses(),
                'production'   =>  $production,
                'batch_no'     => $batch_no
            ];
            return view('production::pre-production-calculation.product-mixing', $data);
        } else {
            return $this->access_blocked();
        }
   }

   public function pre_production_product_mixing_store(ProductMixingRequest $request)
   {
    if ($request->ajax()) {
        // dd($request->all());
        if (permission('product-mixing-add')) {
            DB::beginTransaction();
            try {
                $status = $request->status ? $request->status : 2;
                $production_data = [
                    'batch_no'          => $request->batch_no,
                    'reference_no'      => $request->batch_no,
                    'mfg_date'          => $request->mfg_date,
                    'exp_date'          => $request->exp_date,
                    'date'              => $request->date,
                    'product_id'        => $request->product_id,
                    'formulation_id'    => $request->serial_no,
                    'unit_id'           => $request->unit_id,
                    'total_fg_qty'      => $request->total_fg_qty,
                    'total_cost'        => $request->total_cost,
                    'extra_cost'        => $request->extra_cost ? $request->extra_cost : 0,
                    'total_net_cost'    => $request->total_net_cost,
                    'per_unit_cost'     => $request->per_unit_cost,
                    'pwarehouse_id'     => $request->pwarehouse_id,
                    'mwarehouse_id'     => $request->mwarehouse_id,
                    'status'            => $status,
                    'production_status' => 1,
                    'created_by'        => auth()->user()->id,
                    'created_at'        => date('Y-m-d H:i:s'),
                ];
                $result = Production::create($production_data);
                $production = Production::with('materials', 'packaging_materials')->find($result->id);
                if($result)
                {
                    PreProduction::where('serial_no',$request->reference_no)->update(['status'=>1]);
                }
                //Formulation Materials
                $production_materials = [];
                if ($request->has('materials')) {
                    foreach ($request->materials as $value) {
                        $production_materials[$value['material_id']] = [
                            'unit_id' => $value['unit_id'],
                            'qty'     => $value['qty'],
                            'rate'    => $value['rate'],
                            'total'   => $value['total'],
                        ];

                        if ($production->status == 1) {
                            $material = Material::where([['id', $value['material_id']], ['qty', '>', 0]])->first();
                            if ($material) {
                                $material->qty -= $value['qty'];
                                $material->update();
                            }
                            $warehosue_material = WarehouseMaterial::where([['warehouse_id', $request->mwarehouse_id], ['material_id', $value['material_id']], ['qty', '>', 0]])->first();
                            if ($warehosue_material) {
                                $warehosue_material->qty -= $value['qty'];
                                $warehosue_material->update();
                            }
                        }
                    }
                    $production->materials()->sync($production_materials);
                }

                //Extra Materials Addition or Subtraction
                $production_packaging_materials = [];
                if ($request->has('packaging_materials')) {
                    foreach ($request->packaging_materials as $value) {
                        if(!empty($value['qty'])){
                            $production_packaging_materials[$value['material_id']] = [
                                'prefix'  => $value['prefix'],
                                'unit_id' => $value['unit_id'],
                                'qty'     => $value['qty'],
                                'rate'    => $value['rate'],
                                'total'   => $value['total'],
                            ];

                            if ($production->status == 1) {
                                $material = Material::where([['id', $value['material_id']], ['qty', '>', 0]])->first();
                                if ($material) {
                                    if ($value['prefix'] == '-') {
                                        $material->qty += $value['qty'];
                                    } else {
                                        $material->qty -= $value['qty'];
                                    }
                                    $material->update();
                                }
                                $warehosue_material = WarehouseMaterial::where([['warehouse_id', $request->mwarehouse_id], ['material_id', $value['material_id']], ['qty', '>', 0]])->first();
                                if ($warehosue_material) {
                                    if ($value['prefix'] == '-') {
                                        $warehosue_material->qty += $value['qty'];
                                    } else {
                                        $warehosue_material->qty -= $value['qty'];
                                    }
                                    $warehosue_material->update();
                                }
                            }
                        }
                    }
                    $production->packaging_materials()->sync($production_packaging_materials);
                }
                $output = $this->store_message($result, null);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $th->getMessage()];
            }return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }
   }

}
