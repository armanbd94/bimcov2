<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Product\Entities\Product;
use Modules\Report\Entities\ProductWiseSalesReport;

class ProductWiseSalesReportController extends BaseController
{
    public function __construct(ProductWiseSalesReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('product-wise-sales-report-access')){
            $this->setPageData('Product Wise Sales Report','Product Wise Sales Report','fas fa-file-signature',[['name' => 'Report'],['name' => 'Product Wise Sales Report']]);
            $products = Product::toBase()->get();
            return view('report::product-wise-sales-report.index',compact('products'));
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
                $row = [];
                $row[] = $no;
                $row[] = $value->sale->sale_date;
                $row[] = $value->product->name;
                $row[] = $value->sale->invoice_no;
                $row[] = $value->sale->customer->name;
                $row[] = $value->qty.' '.$value->sale_unit->unit_name;
                $row[] = number_format($value->net_unit_price,2);
                $row[] = number_format($value->total,2);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
