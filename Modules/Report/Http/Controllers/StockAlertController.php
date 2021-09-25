<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Modules\Report\Entities\StockAlert;
use App\Http\Controllers\BaseController;

class StockAlertController extends BaseController
{
    public function __construct(StockAlert $model)
    {
        $this->model = $model;
    }
    public function index()
    {
        if(permission('stock-alert-report-access')){
            $this->setPageData('Stock Alert Report','Stock Alert Report','fas fa-boxes',[['name' => 'Stock Alert Report']]);
            $categories = Category::allProductCategories();
            return view('report::stock-alert.index',compact('categories'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('stock-alert-report-access')){
                if (!empty($request->name)) {
                    $this->model->setName($request->name);
                }
                if (!empty($request->category_id)) {
                    $this->model->setCategoryID($request->category_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->name;
                    $row[] = $value->category->name;
                    $row[] = $value->unit->unit_name;
                    $row[] = $value->qty ?? 0;
                    $row[] = $value->alert_quantity ?? 0;
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }   
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
