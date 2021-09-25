<?php

namespace Modules\Setting\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Setting\Entities\LaborBill;
use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Modules\Setting\Http\Requests\LaborBillFormRequest;

class LaborBillController extends BaseController
{
    public function __construct(LaborBill $model)
    {
        $this->model = $model;
    }

    public function index()
    {              
        if(permission('labor-bill-access')){
            $this->setPageData('Labor Bill','Labor Bill','fas fa-balance-scale',[['name' => 'Labor Bill']]);
            return view('setting::labor-bill.index');
        }else{
            return $this->access_blocked();
        }
    }    

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('labor-bill-access')){

                if (!empty($request->labor_bill_name)) {
                    $this->model->setName($request->labor_bill_name);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('labor-bill-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('labor-bill-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    $row = [];
                    if(permission('labor-bill-bulk-delete')){
                        $row[] = row_checkbox($value->id);
                    }
                    $row[] = $no;
                    $row[] = $value->name;
                    $row[] = permission('labor-bill-edit') ? change_status($value->id,$value->status,$value->name) : STATUS_LABEL[$value->status];
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

    public function store_or_update_data(LaborBillFormRequest $request)
    {
        if($request->ajax()){
            if (permission('labor-bill-add') || permission('labor-bill-edit')){
                $collection   = collect($request->validated());
                $collection   = $this->track_data($collection,$request->update_id);
                $result       = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                $output       = $this->store_message($result, $request->update_id);
               
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }


    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('labor-bill-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
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
            if(permission('labor-bill-delete')){
                $result   = $this->model->find($request->id)->delete();
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
            if(permission('labor-bill-bulk-delete')){
                $result   = $this->model->destroy($request->ids);
                $output   = $this->bulk_delete_message($result);
                $this->model->flushCache();
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
            if(permission('labor-bill-edit')){
                $result   = $this->model->find($request->id)->update(['status' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error','message' => 'Failed To Change Status'];
                $this->model->flushCache();
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function labor_list(Request $request)
    {
        if($request->ajax())
        {
            $laborlist = $this->model->where(['status'=>1])->get();
            $output = '<option value="">Select Please</option>';
            if (!$laborlist->isEmpty()){
                foreach ($laborlist as $labor){
                    $output .=  '<option value="'.$labor->id .'">'. $labor->name.'</option>';
                }
            }
            return $output;
        }
    }
}
