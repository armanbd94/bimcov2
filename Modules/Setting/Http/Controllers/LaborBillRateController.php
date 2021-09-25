<?php

namespace Modules\Setting\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Setting\Entities\LaborBillRate;
use Illuminate\Contracts\Support\Renderable;
use Modules\Setting\Http\Requests\LaborBillRateFormRequest;

class LaborBillRateController extends BaseController
{

    public function __construct(LaborBillRate $model)
    {
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {          
        if(permission('labor-bill-rate-access')){
            $this->setPageData('Labor Bill Rate','Labor Bill Rate','fas fa-balance-scale',[['name' => 'Labor Bill Rate']]);
            return view('setting::labor-bill-rate.index');
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
                    if(permission('labor-bill-rate-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('labor-bill-rate-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    $row = [];
                    if(permission('labor-bill-rate-bulk-delete')){
                        $row[] = row_checkbox($value->id);
                    }
                    $row[] = $no;
                    $row[] = $value->labor_bill->name;
                    $row[] = $value->unit->unit_name;
                    $row[] = $value->rate;
                    $row[] = permission('labor-bill-rate-edit') ? change_status($value->id,$value->status,$value->labor_bill->name) : STATUS_LABEL[$value->status];
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

    public function store_or_update_data(LaborBillRateFormRequest $request)
    {
        if($request->ajax()){
            if (permission('labor-bill-rate-add') || permission('labor-bill-rate-edit')){
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

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('setting::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('setting::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('labor-bill-rate-edit')){
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
            if(permission('labor-bill-rate-delete')){
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
            if(permission('labor-bill-rate-bulk-delete')){
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
            if(permission('labor-bill-rate-edit')){
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
   

    public function labor_bill_rate_list(Request $request)
    {
        if($request->ajax())
        {
            $rates = LaborBillRate::with('unit')->where('labor_bill_id',$request->bill_id)->get();
            $output = '';
            if(!$rates->isEmpty())
            {
                $output .= '<option value="">Select Please</option>';
                foreach ($rates as $key => $value) {
                    $output .= '<option value="'.$value->id.'" data-rate="'.$value->rate.'">'.$value->unit->unit_name.'</option>';
                }
            }
            return $output;
        }
    }
}
