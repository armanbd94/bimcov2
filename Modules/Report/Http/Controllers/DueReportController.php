<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Modules\Report\Entities\DueReport;
use App\Http\Controllers\BaseController;

class DueReportController extends BaseController
{
    public function __construct(DueReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('due-report-access')){
            $this->setPageData('Due Report','Due Report','fas fa-file-signature',[['name' => 'Report','link'=>'javascript::void();'],['name' => 'Due Report']]);
            return view('report::due-report.index');
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
                $row[] = date(config('settings.date_format'),strtotime($value->sale_date));
                $row[] = $value->invoice_no;
                $row[] = $value->name.' - '.$value->mobile;
                $row[] = number_format(($value->grand_total),2, '.', ',');
                $row[] = number_format(($value->previous_due),2, '.', ',');
                $row[] = number_format(($value->paid_amount),2, '.', ',');
                $row[] = number_format(($value->due_amount),2, '.', ',');
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
