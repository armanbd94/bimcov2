<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Customer\Entities\CreditCustomer;

class CreditCustomerController extends BaseController
{
    public function __construct(CreditCustomer $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('credit-customer-access')){
            $this->setPageData('Credit Customer','Credit Customer','fas fa-users',[['name'=>'Customer','link'=>route('customer')],['name'=>'Credit Customer']]);
            $customers = $this->credit_customers();
            return view('customer::credit-customer.index',compact('customers'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){

            if (!empty($request->customer_id)) {
                $this->model->setCustomerID($request->customer_id);
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
                $row[] = $value->company_name;
                $row[] = $value->group_name;
                $row[] = $value->address;
                $row[] = $value->mobile;
                $row[] = $value->email;
                $row[] = $value->city;
                $row[] = $value->zipcode;
                $row[] = number_format(($value->balance),2, '.', ',');
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }

    protected function credit_customers()
    {
       return $data =  DB::table('customers as c')
                    ->selectRaw('c.*, ((select ifnull(sum(debit),0) from transactions where chart_of_account_id= b.id)-(select ifnull(sum(credit),0) from transactions where chart_of_account_id= b.id)) as balance')
                    ->leftjoin('chart_of_accounts as b', 'c.id', '=', 'b.customer_id')
                    ->groupBy('c.id','b.id')
                    ->having('balance','>',0)
                    ->orderBy('c.name','asc')
                    ->get();
    }
}
