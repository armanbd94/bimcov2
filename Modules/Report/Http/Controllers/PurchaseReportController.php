<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\PurchaseReport;

class PurchaseReportController extends BaseController
{

    public function __construct(PurchaseReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('purchase-report-access')){
            $this->setPageData('Purchase Report','Purchase Report','fas fa-file-signature',[['name' => 'Report','link'=>'javascript::void();'],['name' => 'Purchase Report']]);
            return view('report::purchase-report.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->start_date)) {
                $this->model->setStartDate($request->start_date);
            }
            if (!empty($request->end_date)) {
                $this->model->setEndDate($request->end_date);
            }
            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $row = [];
                $row[] = $no;
                $row[] = date(config('settings.date_format'),strtotime($value->purchase_date));
                $row[] = $value->invoice_no;
                $row[] = $value->created_by;
                $row[] = $value->supplier->name.' - '.$value->supplier->mobile;
                $row[] = number_format($value->grand_total,2);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
