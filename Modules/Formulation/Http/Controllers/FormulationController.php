<?php

namespace Modules\Formulation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Formulation\Entities\Formulation;
use Modules\Formulation\Entities\FormulationMaterial;
use Modules\Formulation\Http\Requests\FormulationRequest;

class FormulationController extends BaseController
{
    public function __construct(Formulation $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('formulation-access')){
            $this->setPageData('Formulation List','Formulation List','fab fa-product-hunt',[['name' => 'Formulation List']]);
            $products = Product::toBase()->where('status',1)->get();
            return view('formulation::index',compact('products'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('formulation-access')){
                if (!empty($request->formulation_no)) {
                    $this->model->setFormulationNo($request->formulation_no);
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


                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if (permission('formulation-edit')) {
                        $action .= ' <a class="dropdown-item" href="'.route("formulation.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                        
                    }
                    if (permission('formulation-view')) {
                        $action .= ' <a class="dropdown-item view_data"  data-id="' . $value->id . '">'.self::ACTION_BUTTON['View'].'</a>';
                    }

                    if (permission('formulation-delete')) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->id . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    $row = [];
                    if(permission('formulation-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = '<a class="dropdown-item view_data" style="text-decoration:underline;"  data-id="' . $value->id . '">'.$value->id.'</a>';
                    $row[] = date(config('settings.date_format'),strtotime($value->date));
                    $row[] = $value->product->name.' - '.$value->product->code;
                    $row[] = $value->formulation_no;
                    $row[] = $value->unit->unit_name;
                    $row[] = $value->total_fg_qty;
                    $row[] = $value->total_material_qty;
                    $row[] = number_format($value->total_material_value,4,'.','');
                    $row[] = permission('formulation-edit') ? change_status($value->id,$value->status, $value->id) : STATUS_LABEL[$value->status];
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
        if(permission('formulation-add')){
            $this->setPageData('Add Formulation','Add Formulation','fas fa-plus-square',[['name' => 'Add Formulation']]);
            $serial_no = $this->model->orderBy('id','desc')->first();
            $data = [
                'products'  => Product::with('unit:id,unit_name')->where('status',1)->get(),
                'materials' => Material::with('unit:id,unit_name','category:id,name')->where('status',1)->get(),
                'serial_no' => $serial_no ? $serial_no->id + 1 : 1,
            ];
            return view('formulation::create',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function store_or_update_data(FormulationRequest $request)
    {
        if ($request->ajax()) {
            if(permission('formulation-add')){
                DB::beginTransaction();
                try {
                    $collection = collect($request->validated())->except('materials');
                    $collection = $this->track_data($collection,null);
                    $result     = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                    $formulation = $this->model->with('materials')->find($result->id);
                    $formulation_materials = [];
                    if($request->has('materials')){
                        foreach($request->materials as $value)
                        {
                            $formulation_materials[$value['material_id']] = [
                                'unit_id' => $value['unit_id'],
                                'qty'     => $value['qty'],
                                'rate'    => $value['rate'],
                                'total'   => $value['total'],
                            ];
                        }
                        $formulation->materials()->sync($formulation_materials);
                    }
                    $output  = $this->store_message($result, $request->update_id);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $th->getMessage()];
                }return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function show(Request $request)
    {
        if($request->ajax()){
            if(permission('formulation-view')){
                $formulation = $this->model->with('materials','unit','product')->findOrFail($request->id);
                return view('formulation::view-data',compact('formulation'))->render();
            }
        }
    }


    public function edit(int $id)
    {
        if(permission('formulation-edit')){
            $this->setPageData('Formulation Edit','Formulation Edit','fas fa-edit',[['name' => 'Formulation Edit']]);
            $data = [
                'materials'   => Material::with('unit:id,unit_name','category:id,name')->where('status',1)->get(),
                'products'  => Product::with('unit:id,unit_name')->where('status',1)->get(),
                'formulation' => $this->model->with('materials','unit')->findOrFail($id),
            ];
            return view('formulation::edit',$data);
        }else{
            return $this->access_blocked(); 
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('formulation-delete')){
                $formulation  = $this->model->with('materials')->find($request->id);
                if(!$formulation->materials->isEmpty())
                {
                    $formulation->materials()->detach();
                }
                $result    = $formulation->delete();
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
            if(permission('formulation-bulk-delete')){
                DB::beginTransaction();
                try {
                    FormulationMaterial::whereIn('formulation_id',$request->ids)->delete();
                    $result   = $this->model->destroy($request->ids);
                    $output   = $this->bulk_delete_message($result);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if($request->ajax()){
            if(permission('formulation-edit')){
                $result   = $this->model->find($request->id)->update(['status' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error','message' => 'Failed To Change Status'];
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function generateNumber(Request $request){
        if($request->ajax()){
            $formulation_number = $this->model->where('product_id',$request->product_id)->count();
            return response()->json(($formulation_number > 0) ? ($formulation_number + 1) : 1);
        }
    }

}
