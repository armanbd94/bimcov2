<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Purchase\Entities\Purchase;
use Modules\Report\Entities\SummaryReport;
use Modules\Sale\Entities\Sale;

class SummaryReportController extends BaseController
{

    public function index()
    {
        if(permission('summary-report-access')){
            $this->setPageData('Today Summary Report','Today Summary Report','fas fa-file-signature',[['name' => 'Report','link'=>'javascript::void();'],['name' => 'Today Summary Report']]);
            $data = [
                'sales' => Sale::with('customer:id,name,mobile')
                            ->where('sale_date', date('Y-m-d'))->get(),
                'purchases' => Purchase::with('supplier:id,name,mobile')
                                    ->where('purchase_date', date('Y-m-d'))
                                    ->get(),
            ];
            return view('report::summary-report.index',$data);
        }else{
            return $this->access_blocked();
        }
    }

}
