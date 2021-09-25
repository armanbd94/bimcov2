<?php

namespace Modules\Transfer\Http\Controllers;

use Exception;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;
use Modules\Transfer\Entities\Transfer;
use App\Http\Controllers\BaseController;
use Modules\Material\Entities\WarehouseMaterial;
use Modules\Transfer\Http\Requests\TransferFormRequest;

class TransferController extends BaseController
{
    private const chalan_no = 1001;
    public function __construct(Transfer $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('transfer-access')){
            $this->setPageData('Manage Stock Transfer','Manage Stock Transfer','fas fa-share-square',[['name' => 'Manage Stock Transfer']]);
            $warehouses = Warehouse::activeWarehouses();
            return view('transfer::index',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('transfer-access')){

                if (!empty($request->chalan_no)) {
                    $this->model->setMemoNo($request->chalan_no);
                }
                if (!empty($request->from_date)) {
                    $this->model->setFromDate($request->from_date);
                }
                if (!empty($request->to_date)) {
                    $this->model->setToDate($request->to_date);
                }
                if (!empty($request->from_warehouse_id)) {
                    $this->model->setFromWarehouseID($request->from_warehouse_id);
                }
                if (!empty($request->to_warehouse_id)) {
                    $this->model->setToWarehouseID($request->to_warehouse_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('transfer-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("transfer.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }

                    if(permission('transfer-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("transfer.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }

                    if(permission('transfer-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->chalan_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('transfer-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->chalan_no;
                    $row[] = $value->from_warehouse;
                    $row[] = $value->to_warehouse;
                    $row[] = $value->item;
                    $row[] = number_format($value->total_cost,2);
                    $row[] = $value->shipping_cost ? number_format($value->shipping_cost,2) : 0;
                    $row[] = number_format($value->grand_total,2);
                    $row[] = date(config('settings.date_format'),strtotime($value->transfer_date));
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


    public function create()
    {
        if(permission('transfer-add')){
            $this->setPageData('Add Transfer','Add Transfer','fas fa-shopping-cart',[['name' => 'Add Transfer']]);
            $purchase = $this->model->select('chalan_no')->orderBy('chalan_no','desc')->first();
            $data = [
                'warehouses' => Warehouse::activeWarehouses(),
                'chalan_no'  => $purchase ? 'TRN-'.(explode('TRN-',$purchase->chalan_no)[1] + 1) : 'TRN-1001'
            ];
            return view('transfer::create',$data);
        }else{
            return $this->access_blocked();
        }
        
    }

    public function store(TransferFormRequest $request)
    {
        if($request->ajax()){
            if(permission('transfer-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $transfer_data = [
                        'chalan_no'         => $request->chalan_no,
                        'from_warehouse_id' => $request->from_warehouse_id,
                        'to_warehouse_id'   => $request->to_warehouse_id,
                        'item'              => $request->item,
                        'total_qty'         => $request->total_qty,
                        'total_cost'        => $request->total_cost,
                        'shipping_cost'     => $request->shipping_cost ? $request->shipping_cost : null,
                        'grand_total'       => $request->grand_total,
                        'note'              => $request->note,
                        'transfer_date'     => $request->transfer_date,
                        'received_by'     => $request->received_by,
                        'carried_by'     => $request->carried_by,
                        'created_by'        => auth()->user()->id
                    ];

                    //transfer materials
                    $materials = [];
                    if($request->has('materials'))
                    {
                        foreach ($request->materials as $key => $value) {
                            $unit = Unit::where('unit_name',$value['unit'])->first();
                            if($unit->operator == '*'){
                                $qty = $value['qty'] * $unit->operation_value;
                            }else{
                                $qty = $value['qty'] / $unit->operation_value;
                            }

                            $materials[$value['id']] = [
                                'qty'              => $value['qty'],
                                'transfer_unit_id' => $unit ? $unit->id : null,
                                'net_unit_cost'    => $value['net_unit_cost'],
                                'total'            => $value['subtotal']
                            ];

                            $from_warehouse_material = WarehouseMaterial::where([
                                'warehouse_id' => $request->from_warehouse_id,
                                'material_id'  => $value['id']])->first();
                            if($from_warehouse_material){
                                $from_warehouse_material->qty -= $qty;
                                if($from_warehouse_material->update())
                                {
                                    $to_warehouse_material = WarehouseMaterial::where([
                                        'warehouse_id' => $request->to_warehouse_id,
                                        'material_id'  => $value['id']])->first();
                                    if($to_warehouse_material){
                                        $to_warehouse_material->qty += $qty;
                                        $to_warehouse_material->update();
                                    }else{
                                        WarehouseMaterial::create([
                                            'warehouse_id' => $request->to_warehouse_id,
                                            'material_id'  => $value['id'],
                                            'qty'          => $qty
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                    $result  = $this->model->create($transfer_data);
                    $transfer = $this->model->with('transfer_materials')->find($result->id);
                    if(count($materials) > 0){
                        $transfer->transfer_materials()->sync($materials);
                    }
                    $output  = $this->store_message($result, null);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }



    public function show(int $id)
    {
        if(permission('transfer-view')){
            $this->setPageData('Transfer Details','Transfer Details','fas fa-file',[['name'=>'Transfer','link' => route('purchase')],['name' => 'Transfer Details']]);
            $transfer = $this->model->with('transfer_materials','from_warehouse','to_warehouse')->find($id);
            return view('transfer::details',compact('transfer'));
        }else{
            return $this->access_blocked();
        }
    }
    public function edit(int $id)
    {

        if(permission('transfer-edit')){
            $this->setPageData('Edit Transfer','Edit Transfer','fas fa-edit',[['name'=>'Transfer','link' => route('purchase')],['name' => 'Edit Transfer']]);
            $data = [
                'transfer'   => $this->model->with('transfer_materials')->find($id),
                'warehouses' => Warehouse::activeWarehouses(),
            ];
            return view('transfer::edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(TransferFormRequest $request)
    {
        if($request->ajax()){
            if(permission('transfer-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $transferData = $this->model->with('transfer_materials')->find($request->transfer_id);

                    $transfer_data = [
                        'from_warehouse_id' => $request->from_warehouse_id,
                        'to_warehouse_id'   => $request->to_warehouse_id,
                        'item'              => $request->item,
                        'total_qty'         => $request->total_qty,
                        'total_cost'        => $request->total_cost,
                        'shipping_cost'     => $request->shipping_cost ? $request->shipping_cost : null,
                        'grand_total'       => $request->grand_total,
                        'note'              => $request->note,
                        'transfer_date'     => $request->transfer_date,
                        'received_by'       => $request->received_by,
                        'carried_by'        => $request->carried_by,
                        'modified_by'       => auth()->user()->id
                    ];

                    if(!$transferData->transfer_materials->isEmpty())
                    {
                        foreach ($transferData->transfer_materials as  $transfer_material) {
                            
                            $old_transfered_qty = $transfer_material->pivot->qty;
                            $transfer_unit = Unit::find($transfer_material->pivot->transfer_unit_id);
                            if($transfer_unit->operator == '*'){
                                $old_transfered_qty = $old_transfered_qty * $transfer_unit->operation_value;
                            }else{
                                $old_transfered_qty = $old_transfered_qty / $transfer_unit->operation_value;
                            }

                            $to_warehouse_material = WarehouseMaterial::where([
                                ['warehouse_id',$transferData->to_warehouse_id],
                                ['material_id',$transfer_material->id],['qty','>',0]])->first();
                            if($to_warehouse_material){
                                $to_warehouse_material->qty -= $old_transfered_qty;
                                if($to_warehouse_material->update())
                                {
                                    $from_warehouse_material = WarehouseMaterial::where([
                                        ['warehouse_id',$transferData->from_warehouse_id],
                                        ['material_id',$transfer_material->id]])->first();
                                    if($from_warehouse_material)
                                    {
                                        $from_warehouse_material->qty += $old_transfered_qty;
                                        $from_warehouse_material->update();
                                    }
                                }
                            }
                        }
                    }
                     //transfer materials
                     $materials = [];
                     if($request->has('materials'))
                     {
                         foreach ($request->materials as $key => $value) {
                             $unit = Unit::where('unit_name',$value['unit'])->first();
                             if($unit->operator == '*'){
                                 $qty = $value['qty'] * $unit->operation_value;
                             }else{
                                 $qty = $value['qty'] / $unit->operation_value;
                             }
 
                             $materials[$value['id']] = [
                                 'qty'              => $value['qty'],
                                 'transfer_unit_id' => $unit ? $unit->id : null,
                                 'net_unit_cost'    => $value['net_unit_cost'],
                                 'total'            => $value['subtotal']
                             ];
 
                             $from_warehouse_material = WarehouseMaterial::where([
                                 'warehouse_id' => $request->from_warehouse_id,
                                 'material_id'  => $value['id']])->first();
                             if($from_warehouse_material){
                                 $from_warehouse_material->qty -= $qty;
                                 if($from_warehouse_material->update())
                                 {
                                     $to_warehouse_material = WarehouseMaterial::where([
                                         'warehouse_id' => $request->to_warehouse_id,
                                         'material_id'  => $value['id']])->first();
                                     if($to_warehouse_material){
                                         $to_warehouse_material->qty += $qty;
                                         $to_warehouse_material->update();
                                     }else{
                                         WarehouseMaterial::create([
                                             'warehouse_id' => $request->to_warehouse_id,
                                             'material_id'  => $value['id'],
                                             'qty'          => $qty
                                         ]);
                                     }
                                 }
                             }
                         }
                     }

                    $transfer = $transferData->update($transfer_data);
                    $transferData->transfer_materials()->sync($materials);
                    
                    $output  = $this->store_message($transfer, $request->purchase_id);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
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
            if(permission('transfer-delete')){
                DB::beginTransaction();
                try {
                    $transferData = $this->model->with('transfer_materials')->find($request->id);

                    if(!$transferData->transfer_materials->isEmpty())
                    {
                        foreach ($transferData->transfer_materials as  $transfer_material) {
                            
                            $old_transfered_qty = $transfer_material->pivot->qty;
                            $transfer_unit = Unit::find($transfer_material->pivot->transfer_unit_id);
                            if($transfer_unit->operator == '*'){
                                $old_transfered_qty = $old_transfered_qty * $transfer_unit->operation_value;
                            }else{
                                $old_transfered_qty = $old_transfered_qty / $transfer_unit->operation_value;
                            }

                            $to_warehouse_material = WarehouseMaterial::where([
                                ['warehouse_id',$transferData->to_warehouse_id],
                                ['material_id',$transfer_material->id],['qty','>',0]])->first();
                            if($to_warehouse_material){
                                $to_warehouse_material->qty -= $old_transfered_qty;
                                if($to_warehouse_material->update())
                                {
                                    $from_warehouse_material = WarehouseMaterial::where([
                                        ['warehouse_id',$transferData->from_warehouse_id],
                                        ['material_id',$transfer_material->id]])->first();
                                    if($from_warehouse_material)
                                    {
                                        $from_warehouse_material->qty += $old_transfered_qty;
                                        $from_warehouse_material->update();
                                    }
                                }
                            }
                        }
                        $transferData->transfer_materials()->detach();
                    }

                    $result = $transferData->delete();
                    $output = $result ? ['status' => 'success','message' => 'Data has been deleted successfully'] : ['status' => 'error','message' => 'failed to delete data'];
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status'=>'error','message'=>$e->getMessage()];
                }
                return response()->json($output);
            }else{
                $output = $this->access_blocked();
            }
            return response()->json($output);
        }else{
            return response()->json($this->access_blocked());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('transfer-bulk-delete')){
                
                    DB::beginTransaction();
                    try {
                        foreach ($request->ids as $id) {
                            $transferData = $this->model->with('transfer_materials')->find($id);

                            if(!$transferData->transfer_materials->isEmpty())
                            {
                                foreach ($transferData->transfer_materials as  $transfer_material) {
                                    
                                    $old_transfered_qty = $transfer_material->pivot->qty;
                                    $transfer_unit = Unit::find($transfer_material->pivot->transfer_unit_id);
                                    if($transfer_unit->operator == '*'){
                                        $old_transfered_qty = $old_transfered_qty * $transfer_unit->operation_value;
                                    }else{
                                        $old_transfered_qty = $old_transfered_qty / $transfer_unit->operation_value;
                                    }

                                    $to_warehouse_material = WarehouseMaterial::where([
                                        ['warehouse_id',$transferData->to_warehouse_id],
                                        ['material_id',$transfer_material->id],['qty','>',0]])->first();
                                    if($to_warehouse_material){
                                        $to_warehouse_material->qty -= $old_transfered_qty;
                                        if($to_warehouse_material->update())
                                        {
                                            $from_warehouse_material = WarehouseMaterial::where([
                                                ['warehouse_id',$transferData->from_warehouse_id],
                                                ['material_id',$transfer_material->id]])->first();
                                            if($from_warehouse_material)
                                            {
                                                $from_warehouse_material->qty += $old_transfered_qty;
                                                $from_warehouse_material->update();
                                            }
                                        }
                                    }
                                }
                                $transferData->transfer_materials()->detach();
                            }
                            $result = $transferData->delete();
                        }
                        $output = $result ? ['status' => 'success','message' => 'Data has been deleted successfully'] : ['status' => 'error','message' => 'failed to delete data'];
                        DB::commit();
                    } catch (Exception $e) {
                        DB::rollBack();
                        $output = ['status'=>'error','message'=>$e->getMessage()];
                    }
                    return response()->json($output);
                
            }else{
                $output = $this->access_blocked();
            }
            return response()->json($output);
        }else{
            return response()->json($this->access_blocked());
        }
    }

}
