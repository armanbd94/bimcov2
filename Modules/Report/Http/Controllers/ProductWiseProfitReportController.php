<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\ProductWiseProfitReport;

class ProductWiseProfitReportController extends BaseController
{
    public function __construct(ProductWiseProfitReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('product-wise-profit-report-access')){
            $this->setPageData('Product Wise Profit Report','Product Wise Profit Report','fas fa-file-signature',[['name' => 'Report'],['name' => 'Product Wise Profit Report']]);
            $products = $this->model->pluck('name','id');
            return view('report::product-wise-profit-report.index',compact('products'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->product_id)) {
                $this->model->setProductID($request->product_id);
            }
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
                $product_total_qty     = ($value->total_production_qty ? $value->total_production_qty : 0 ) + ($value->opening_stock_qty ? $value->opening_stock_qty : 0);
                $product_total_cost    = ($value->opening_cost ?? 0) * ($value->opening_stock_qty ?? 0) + ($value->total_production_cost ?? 0);
                $product_remaining_qty = $product_total_qty - ($value->total_sold_qty ?? 0);
                $profit_amount         = (($value->total_sale_amount ?? 0) - $product_total_cost);
                if($product_total_qty > 0){
                    $status = ($profit_amount > 0) ? '<span  class="label label-success label-pill label-inline" style="min-width:70px !important;">Profit</span>' : '<span  class="label label-danger label-pill label-inline" style="min-width:70px !important;">No Profit</span>';
                }else{
                    $status = 'N/A';
                }
                $row = [];
                $row[] = $no;
                $row[] = $value->name;
                $row[] = $value->code;
                $row[] = $value->unit_name;
                $row[] = number_format($product_total_qty,4,'.','');
                $row[] = number_format($product_total_cost,4,'.','');
                $row[] = number_format(($value->total_sold_qty ?? 0),4,'.','');
                $row[] = number_format(($value->total_sale_amount ?? 0),4,'.','');
                $row[] = number_format(($product_remaining_qty ?? 0),4,'.','');
                $row[] = number_format(($profit_amount > 0 ? $profit_amount : 0),4,'.','');
                $row[] = $status;
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
