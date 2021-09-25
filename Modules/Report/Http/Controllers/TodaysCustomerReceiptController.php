<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\TodaysCustomerReceipt;

class TodaysCustomerReceiptController extends BaseController
{
    public function __construct(TodaysCustomerReceipt $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('todays-customer-receipt-access')){
            $this->setPageData('Todays Customer Receipt','Todays Customer Received','fas fa-file-signature',[['name' => 'Report','link'=>'javascript::void();'],['name' => 'Received From Customer']]);
            return view('report::todays-customer-receipt.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $row = [];
                $row[] = $no;
                $row[] = $value->customer_name.' - '.$value->mobile;
                $row[] = $value->description;
                $row[] = number_format($value->credit,2);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
