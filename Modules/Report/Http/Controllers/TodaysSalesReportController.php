<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\TodaysSalesReport;

class TodaysSalesReportController extends BaseController
{

    public function __construct(TodaysSalesReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('todays-sales-report-access')){
            $this->setPageData('Todays Sales Report','Todays Sales Report','fas fa-file-signature',[['name' => 'Report','link'=>'javascript::void();'],['name' => 'Todays Sales Report']]);
            return view('report::todays-sales-report.index');
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
                $row[] = date(config('settings.date_format'),strtotime($value->sale_date));
                $row[] = $value->invoice_no;
                $row[] = $value->created_by;
                $row[] = $value->customer->name.' - '.$value->customer->mobile;
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
