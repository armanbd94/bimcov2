<?php

namespace Modules\Production\Http\Controllers;

use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\Formulation\Entities\Formulation;
use Modules\Material\Entities\Material;
use Modules\Material\Entities\WarehouseMaterial;
use Modules\Production\Entities\Production;
use Modules\Production\Http\Requests\ProductionRequest;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\WarehouseProduct;
use Modules\Setting\Entities\Warehouse;

class ProductionController extends BaseController
{

    public function __construct(Production $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('product-mixing-access')) {
            $this->setPageData('Product Mixing List', 'Product Mixing List', 'fab fa-product-hunt', [['name' => 'Product Mixing List']]);
            $products = Product::toBase()->where('status', 1)->get();
            return view('production::production.index', compact('products'));
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('product-mixing-access')) {
                if (!empty($request->batch_no)) {
                    $this->model->setBatchNo($request->batch_no);
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
                    if (permission('product-mixing-edit')) {
                        if ($value->production_status != 4 && $value->status != 1) {
                            $action .= ' <a class="dropdown-item" href="' . route("product.mixing.edit", $value->id) . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                        }
                    }

                    if (permission('product-mixing-approve')) {
                        if ($value->status != 1) {
                            $action .= ' <a class="dropdown-item change_status"  data-id="' . $value->id . '" data-name="' . $value->batch_no . '" data-status="' . $value->status . '"><i class="fas fa-check-circle text-success mr-2"></i> Change Approve Status</a>';
                        }
                    }

                    if (permission('product-mixing-view')) {
                        $action .= ' <a class="dropdown-item"  href="' . url("product-mixing/view", $value->id) . '">' . self::ACTION_BUTTON['View'] . '</a>';
                    }

                    if (permission('product-mixing-edit')) {
                        if ($value->status == 1 && $value->production_status == 1) {
                            $action .= ' <a class="dropdown-item change_production_status"  data-id="' . $value->id . '" data-name="' . $value->batch_no . '" data-status="' . $value->production_status . '"><i class="fas fa-check-circle text-dark mr-2"></i> Change Production Status</a>';
                        }
                    }

                    if (permission('product-mixing-delete')) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->batch_no . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                    }

                    $row = [];
                    if (permission('product-mixing-bulk-delete')) {
                        $row[] = row_checkbox($value->id); //custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    if (permission('product-mixing-view')) {
                        $row[] = '<a class="dropdown-item" style="text-decoration:underline;" href="' . url("product-mixing/view", $value->id) . '">' . $value->batch_no . '</a>';
                    } else {
                        $row[] = $value->batch_no;
                    }
                    $row[] = date(config('settings.date_format'), strtotime($value->date));
                    $row[] = $value->product->name . ' - ' . $value->product->code;
                    $row[] = $value->formulation_id;
                    $row[] = $value->formulation->formulation_no;
                    $row[] = $value->unit->unit_name;
                    $row[] = $value->total_fg_qty;
                    $row[] = number_format($value->total_cost, 4, '.', '');
                    $row[] = number_format($value->extra_cost, 4, '.', '');
                    $row[] = number_format($value->total_net_cost, 4, '.', '');
                    $row[] = number_format($value->per_unit_cost, 4, '.', '');
                    $row[] = APPROVE_STATUS_LABEL[$value->status];
                    $row[] = PRODUCTION_STATUS_LABEL[$value->production_status];
                    $row[] =  $value->reference_no ? $value->reference_no : 'N/A';
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
        if (permission('product-mixing-add')) {
            $this->setPageData('Product Mixing', 'Product Mixing', 'fas fa-plus-square', [['name' => 'Product Mixing']]);
            $data = [
                'materials' => Material::with('unit:id,unit_name', 'category:id,name')->where('status', 1)->get(),
                'products' => Product::with('unit:id,unit_name')->where('status', 1)->get(),
                'warehouses' => Warehouse::activeWarehouses(),
            ];
            // dd($data);
            return view('production::production.create', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function store(ProductionRequest $request)
    {
        if ($request->ajax()) {
            // dd($request->all());
            if (permission('product-mixing-add')) {
                DB::beginTransaction();
                try {
                    $status = $request->status ? $request->status : 2;
                    $production_data = [
                        'batch_no'          => $request->batch_no,
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
                    $result = $this->model->create($production_data);
                    $production = $this->model->with('materials', 'packaging_materials')->find($result->id);
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

    public function show(int $id)
    {
        if (permission('product-mixing-view')) {
            $this->setPageData('Product Mixing Details', 'Product Mixing Details', 'fas fa-eye', [['name' => 'Product Mixing Details']]);
            $data = [
                'production' => $this->model->with('materials', 'unit', 'product', 'formulation','packaging_materials')->findOrFail($id),
            ];
            return view('production::production.details', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function edit(int $id)
    {
        if (permission('product-mixing-edit')) {
            $this->setPageData('Product Mixing Edit', 'Product Mixing Edit', 'fas fa-edit', [['name' => 'Product Mixing Edit']]);
            $production = $this->model->with('materials', 'unit','packaging_materials')->findOrFail($id);
            if ($production->production_status != 4 && $production->status != 1) {
                $data = [
                    'materials'    => Material::with('unit:id,unit_name', 'category:id,name')->where('status', 1)->get(),
                    'products'     => Product::with('unit:id,unit_name')->where('status', 1)->get(),
                    'warehouses'   => Warehouse::activeWarehouses(),
                    'production'   => $production,
                    'formulations' => Formulation::with('unit')->where('product_id', $production->product_id)->get(),
                ];
                return view('production::production.edit', $data);
            } else {
                return $this->access_blocked();
            }
        } else {
            return $this->access_blocked();
        }
    }

    public function update(ProductionRequest $request)
    {
        if ($request->ajax()) {
            if (permission('product-mixing-edit')) {
                DB::beginTransaction();
                try {
                    $production = $this->model->with('materials', 'packaging_materials')->find($request->update_id);
                    $status = $request->status ? $request->status : $production->status;
                    $production_data = [
                        'batch_no'       => $request->batch_no,
                        'mfg_date'       => $request->mfg_date,
                        'exp_date'       => $request->exp_date,
                        'date'           => $request->date,
                        'product_id'     => $request->product_id,
                        'formulation_id' => $request->serial_no,
                        'unit_id'        => $request->unit_id,
                        'total_fg_qty'   => $request->total_fg_qty,
                        'total_cost'     => $request->total_cost,
                        'extra_cost'     => $request->extra_cost ? $request->extra_cost : 0,
                        'total_net_cost' => $request->total_net_cost,
                        'per_unit_cost'  => $request->per_unit_cost,
                        'pwarehouse_id'  => $request->pwarehouse_id,
                        'mwarehouse_id'  => $request->mwarehouse_id,
                        'status'         => $status,
                        'modified_by'    => auth()->user()->id,
                        'updated_at'     => date('Y-m-d H:i:s'),
                    ];

                    if ($production->status == 1) {
                        //Formulation Materials
                        if (!$production->materials->isEmpty()) {
                            foreach ($production->materials as $material) {

                                $warehouse_material = WarehouseMaterial::where([
                                    ['warehouse_id', $production->mwarehouse_id],
                                    ['material_id', $material->id],
                                ])->first();
                                if ($warehouse_material) {
                                    $warehouse_material->qty += $material->pivot->qty;
                                    $warehouse_material->update();
                                }

                                //Remove qty from material
                                $material_data = Material::find($material->id);
                                if ($material_data) {
                                    $material_data->qty += $material->pivot->qty;
                                    $material_data->update();
                                }
                            }
                        }

                        //Extra Materials Addition or Subtraction
                        if (!$production->packaging_materials->isEmpty()) {
                            foreach ($production->packaging_materials as $material) {

                                $warehouse_material = WarehouseMaterial::where([
                                    ['warehouse_id', $production->mwarehouse_id],
                                    ['material_id', $material->id],
                                ])->first();
                                if ($warehouse_material) {
                                    if ($material->pivot->prefix == '-') {
                                        $warehouse_material->qty -= $material->pivot->qty;
                                    } else {
                                        $warehouse_material->qty += $material->pivot->qty;
                                    }
                                    $warehouse_material->update();
                                }

                                //Remove qty from material
                                $material_data = Material::find($material->id);
                                if ($material_data) {
                                    if ($material->pivot->prefix == '-') {
                                        $material_data->qty -= $material->pivot->qty;
                                    } else {
                                        $material_data->qty += $material->pivot->qty;
                                    }
                                    $material_data->update();
                                }
                            }
                        }
                    }

                    $production_materials = [];
                    if ($request->has('materials')) {
                        foreach ($request->materials as $value) {
                            $production_materials[$value['material_id']] = [
                                'unit_id' => $value['unit_id'],
                                'qty'     => $value['qty'],
                                'rate'    => $value['rate'],
                                'total'   => $value['total'],
                            ];

                            if ($status == 1) {
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

                                if ($status == 1) {
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
                        if ($productionData->status == 1) {
                            foreach ($productionData->materials as $material) {
                                $warehouse_material = WarehouseMaterial::where([
                                    ['warehouse_id', $productionData->mwarehouse_id],
                                    ['material_id', $material->id],
                                ])->first();
                                if ($warehouse_material) {
                                    $warehouse_material->qty += $material->pivot->qty;
                                    $warehouse_material->update();
                                }

                                //Remove qty from material
                                $material_data = Material::find($material->id);
                                if ($material_data) {
                                    $material_data->qty += $material->pivot->qty;
                                    $material_data->update();
                                }
                            }
                        }
                        $productionData->materials()->detach();
                    }

                    //Extra Materials Addition or Subtraction
                    if (!$productionData->packaging_materials->isEmpty()) {
                        if ($productionData->status == 1) {
                            foreach ($productionData->packaging_materials as $material) {

                                $warehouse_material = WarehouseMaterial::where([
                                    ['warehouse_id', $productionData->mwarehouse_id],
                                    ['material_id', $material->id],
                                ])->first();
                                if ($warehouse_material) {
                                    if ($material->pivot->prefix == '-') {
                                        $warehouse_material->qty -= $material->pivot->qty;
                                    } else {
                                        $warehouse_material->qty += $material->pivot->qty;
                                    }
                                    $warehouse_material->update();
                                }

                                //Remove qty from material
                                $material_data = Material::find($material->id);
                                if ($material_data) {
                                    if ($material->pivot->prefix == '-') {
                                        $material_data->qty -= $material->pivot->qty;
                                    } else {
                                        $material_data->qty += $material->pivot->qty;
                                    }
                                    $material_data->update();
                                }
                            }
                        }
                        $productionData->packaging_materials()->detach();
                    }

                    if ($productionData->production_status == 2 && $productionData->status == 1) {

                        $warehouse_product = WarehouseProduct::where([
                            ['warehouse_id', $productionData->pwarehouse_id],
                            ['product_id', $productionData->product_id],
                        ])->first();
                        if ($warehouse_product) {
                            $warehouse_product->qty -= $productionData->total_fg_qty;
                            $warehouse_product->update();
                        }

                        //Remove qty from material
                        $product_data = Product::find($productionData->product_id);

                        if ($product_data) {
                            $product_data->qty -= $productionData->total_fg_qty;
                            $product_data->cost = ($product_data->cost > 0) ? (($product_data->cost * 2) - $productionData->per_unit_cost) : $product_data->cost;
                            $product_data->update();
                        }
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
                                if ($productionData->status == 1) {
                                    foreach ($productionData->materials as $material) {
                                        $warehouse_material = WarehouseMaterial::where([
                                            ['warehouse_id', $productionData->mwarehouse_id],
                                            ['material_id', $material->id],
                                        ])->first();
                                        if ($warehouse_material) {
                                            $warehouse_material->qty += $material->pivot->qty;
                                            $warehouse_material->update();
                                        }

                                        //Remove qty from material
                                        $material_data = Material::find($material->id);
                                        if ($material_data) {
                                            $material_data->qty += $material->pivot->qty;
                                            $material_data->update();
                                        }
                                    }
                                }
                                $productionData->materials()->detach();
                            }

                            //Extra Materials Addition or Subtraction
                            if (!$productionData->packaging_materials->isEmpty()) {
                                if ($productionData->status == 1) {
                                    foreach ($productionData->packaging_materials as $material) {

                                        $warehouse_material = WarehouseMaterial::where([
                                            ['warehouse_id', $productionData->mwarehouse_id],
                                            ['material_id', $material->id],
                                        ])->first();
                                        if ($warehouse_material) {
                                            if ($material->pivot->prefix == '-') {
                                                $warehouse_material->qty -= $material->pivot->qty;
                                            } else {
                                                $warehouse_material->qty += $material->pivot->qty;
                                            }
                                            $warehouse_material->update();
                                        }

                                        //Remove qty from material
                                        $material_data = Material::find($material->id);
                                        if ($material_data) {
                                            if ($material->pivot->prefix == '-') {
                                                $material_data->qty -= $material->pivot->qty;
                                            } else {
                                                $material_data->qty += $material->pivot->qty;
                                            }
                                            $material_data->update();
                                        }
                                    }
                                }
                                $productionData->packaging_materials()->detach();
                            }

                            if ($productionData->production_status == 2 && $productionData->status == 1) {

                                $warehouse_product = WarehouseProduct::where([
                                    ['warehouse_id', $productionData->pwarehouse_id],
                                    ['product_id', $productionData->product_id],
                                ])->first();
                                if ($warehouse_product) {
                                    $warehouse_product->qty -= $productionData->total_fg_qty;
                                    $warehouse_product->update();
                                }

                                //Remove qty from material
                                $product_data = Product::find($productionData->product_id);
                                if ($product_data) {
                                    $product_data->qty -= $productionData->total_fg_qty;
                                    $product_data->update();
                                }
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

    public function change_status(Request $request)
    {
        if ($request->ajax()) {
            if (permission('product-mixing-approve')) {
                if ($request->approve_status) {
                    DB::beginTransaction();
                    try {
                        $productionData = $this->model->with('materials', 'packaging_materials')->find($request->production_id);
                        if ($request->approve_status == 1) {
                            if (!$productionData->materials->isEmpty()) {
                                foreach ($productionData->materials as $material) {
                                    $warehouse_material = WarehouseMaterial::where([
                                        ['warehouse_id', $productionData->mwarehouse_id],
                                        ['material_id', $material->id],
                                    ])->first();
                                    if ($warehouse_material) {
                                        $warehouse_material->qty -= $material->pivot->qty;
                                        $warehouse_material->update();
                                    }

                                    //Remove qty from material
                                    $material_data = Material::find($material->id);
                                    if ($material_data) {
                                        $material_data->qty -= $material->pivot->qty;
                                        $material_data->update();
                                    }
                                }
                            }

                            //Extra Materials Addition or Subtraction
                            if (!$productionData->packaging_materials->isEmpty()) {
                                foreach ($productionData->packaging_materials as $material) {

                                    $warehouse_material = WarehouseMaterial::where([
                                        ['warehouse_id', $productionData->mwarehouse_id],
                                        ['material_id', $material->id],
                                    ])->first();
                                    if ($warehouse_material) {
                                        if ($material->pivot->prefix == '-') {
                                            $warehouse_material->qty += $material->pivot->qty;
                                        } else {
                                            $warehouse_material->qty -= $material->pivot->qty;
                                        }
                                        $warehouse_material->update();
                                    }

                                    //Remove qty from material
                                    $material_data = Material::find($material->id);
                                    if ($material_data) {
                                        if ($material->pivot->prefix == '-') {
                                            $material_data->qty += $material->pivot->qty;
                                        } else {
                                            $material_data->qty -= $material->pivot->qty;
                                        }
                                        $material_data->update();
                                    }
                                }
                            }
                        }
                        $productionData->status = $request->approve_status;
                        $productionData->modified_by = auth()->user()->id;
                        $productionData->updated_at = date('Y-m-d');
                        if ($productionData->update()) {
                            $output = ['status' => 'success', 'message' => 'Approval Status Changed Successfully'];
                        } else {
                            $output = ['status' => 'error', 'message' => 'Failed To Change Approval Status'];
                        }
                        DB::commit();
                    } catch (\Throwable $th) {
                        DB::rollback();
                        $output = ['status' => 'error', 'message' => $th->getMessage()];
                    }
                } else {
                    $output = ['status' => 'error', 'message' => 'Please select status'];
                }
                return response()->json($output);
            }
        }
    }

    public function change_production_status(Request $request)
    {
        if ($request->ajax()) {
            if (permission('product-mixing-edit')) {
                if ($request->production_status) {
                    DB::beginTransaction();
                    try {
                        $productionData = $this->model->find($request->production_id);
                        if ($request->production_status == 2 && $productionData->status == 1) {

                            $warehouse_product = WarehouseProduct::where([
                                ['warehouse_id', $productionData->pwarehouse_id],
                                ['product_id', $productionData->product_id],
                            ])->first();
                            if ($warehouse_product) {
                                $warehouse_product->qty += $productionData->total_fg_qty;
                                $warehouse_product->update();
                            } else {
                                WarehouseProduct::create([
                                    'warehouse_id' => $productionData->pwarehouse_id,
                                    'product_id' => $productionData->product_id,
                                    'qty' => $productionData->total_fg_qty,
                                ]);
                            }

                            //Remove qty from material
                            $product_data = Product::find($productionData->product_id);
                            $new_cost = $product_data->cost > 0 ? (($product_data->cost + $productionData->per_unit_cost) / 2) : $productionData->per_unit_cost;
                            if ($product_data) {
                                $product_data->qty += $productionData->total_fg_qty;
                                $product_data->cost = $new_cost;
                                $product_data->update();
                            }

                        }
                        $productionData->production_status = $request->production_status;
                        $productionData->modified_by = auth()->user()->id;
                        $productionData->updated_at = date('Y-m-d');
                        if ($productionData->update()) {
                            $output = ['status' => 'success', 'message' => 'Production Status Changed Successfully'];
                        } else {
                            $output = ['status' => 'error', 'message' => 'Failed To Change Production Status'];
                        }
                        DB::commit();
                    } catch (\Throwable $th) {
                        DB::rollback();
                        $output = ['status' => 'error', 'message' => $th->getMessage()];
                    }
                } else {
                    $output = ['status' => 'error', 'message' => 'Please select status'];
                }
                return response()->json($output);
            }
        }
    }

    public function generate_batch_no($product_id)
    {
        $serial_no = $this->model->whereDate('created_at', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'))->whereDate('created_at', '<=', Carbon::now()->endOfMonth()->format('Y-m-d'))->count();
        $batch_no = $product_id . ($serial_no ? sprintf("%02d", ($serial_no + 1)) : sprintf("%02d", 1)) . date('my');
        return response()->json($batch_no);
    }

    public function check_material_stock(Request $request)
    {
        if ($request->ajax()) {
            $formulation_materials = [];
            $below_qty = 0;
            if ($request->has('materials')) {
                foreach ($request->materials as $value) {
                    $material       = Material::find($value['material_id']);
                    $material_stock = WarehouseMaterial::where([['material_id', $value['material_id']], ['warehouse_id', $request->mwarehouse_id]])->first();
                    $stock_qty      = 0;
                    $background     = '';
                    if ($material_stock) {
                        if ($request->has('update_id')) {
                            $stock_qty = $material_stock->qty + (DB::table('production_materials')->where([['production_id', $request->update_id], ['material_id', $value['material_id']]])->value('qty') ?? 0);
                        } else {
                            $stock_qty = $material_stock->qty;
                        }

                        if ($stock_qty < $value['qty']) {
                            $background = 'bg-danger';
                            $below_qty++;
                        }
                    } else {
                        $background = 'bg-danger';
                        $below_qty++;
                    }
                    $formulation_materials[] = [
                        'material_id'   => $material ? $material->id : '',
                        'material_name' => $material ? $material->material_code . ' - ' . $material->material_name : '',
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
                'materials' => $formulation_materials,
                'below_qty' => $below_qty,
            ];

            if ($below_qty > 0) {
                return view('production::production.material-stock-data', $data)->render();
            } else {
                return response()->json(['status' => 'success']);
            }

        }
    }

    public function formulationData(Request $request)
    {
        if ($request->ajax()) {
            $product_id = $request->product_id;
            $formulation_id = $request->formulation_id;
            $type = $request->type;
            if ($type == 'active') {
                $formulations = Formulation::with('materials')->where([['product_id', $product_id], ['id', $formulation_id]])->get();
            } else {
                $formulations = Formulation::with('materials')->where('product_id', $product_id)->get();
            }

            return view('production::production.formulation-data', compact('formulations'))->render();

        }
    }

}
