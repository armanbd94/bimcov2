<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class DueReport extends BaseModel
{
    
    protected $table = 'sales';

    protected $fillable = [ 'invoice_no', 'warehouse_id', 'customer_id', 'item', 'total_qty', 'total_discount', 'total_tax',
    'total_labor_cost', 'total_price', 'order_tax_rate', 'order_tax', 'order_discount', 'shipping_cost', 'grand_total', 
    'paid_amount', 'due_amount', 'previous_due', 'payment_status', 'payment_method', 'account_id', 'document', 'note',
    'sale_date', 'delivery_status', 'delivery_date', 'status', 'status_change_by', 'status_change_date', 'created_by', 'modified_by'
    ];


    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $order = ['s.sale_date' => 'desc'];
    protected $start_date; 
    protected $end_date; 

    //methods to set custom search property value

    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['s,id','s.sale_date', 's.invoice_no','s.customer_id','s.grand_total','s.previous_due','s.paid_amount','s.due_amount'];
        
        
        $query = DB::table('sales as s')
        ->selectRaw("s.*, c.name, c.mobile")
        ->leftjoin('customers as c','s.customer_id','=','c.id')
        ->where('s.payment_status','<>',1);
        //search query
        if (!empty($this->start_date)) {
            $query->where('s.sale_date', '>=',$this->start_date);
        }
        if (!empty($this->end_date)) {
            $query->where('s.sale_date', '<=',$this->end_date);
        }

        //order by data fetching code
        if (isset($this->orderValue) && isset($this->dirValue)) { //orderValue is the index number of table header and dirValue is asc or desc
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue); //fetch data order by matching column
        } else if (isset($this->order)) {
            $query->orderBy(key($this->order), $this->order[key($this->order)]);
        }
        return $query;
    }

    public function getDatatableList()
    {
        $query = $this->get_datatable_query();
        if ($this->lengthVlaue != -1) {
            $query->offset($this->startVlaue)->limit($this->lengthVlaue);
        }
        return $query->get();
    }

    public function count_filtered()
    {
        $query = $this->get_datatable_query();
        return $query->get()->count();
    }

    public function count_all()
    {
        return DB::table('sales as s')
        ->selectRaw("s.*, c.name, c.mobile")
        ->leftjoin('customers as c','s.customer_id','=','c.id')
        ->where('s.payment_status','<>',1)->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
