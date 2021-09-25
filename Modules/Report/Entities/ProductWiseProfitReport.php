<?php

namespace Modules\Report\Entities;

use App\Models\Unit;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class ProductWiseProfitReport extends BaseModel
{
    protected $table = 'products';
    protected $guarded = [];

    //custom search column property 
    protected $_product_id; 
    protected $_start_date; 
    protected $_end_date; 

    //methods to set custom search property value
    public function setProductID($product_id)
    {
        $this->_product_id = $product_id;
    }
    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['id',null,null,null,null,null,null,null,null,null];
        
        $query = DB::table("products")
        ->leftjoin('units','products.unit_id','=','units.id')
        ->select("products.*","units.unit_name",
                  DB::raw("(SELECT SUM(productions.total_fg_qty) FROM productions
                              WHERE productions.product_id = products.id AND productions.production_status = 2
                              GROUP BY productions.product_id) as total_production_qty"),
                  DB::raw("(SELECT SUM(productions.total_net_cost) FROM productions
                              WHERE productions.product_id = products.id AND productions.production_status = 2
                              GROUP BY productions.product_id) as total_production_cost"),
                  DB::raw("(SELECT SUM(sp.qty) FROM sale_products as sp
                              WHERE sp.product_id = products.id 
                              GROUP BY sp.product_id) as total_sold_qty"),
                  DB::raw("(SELECT SUM(sp.total) FROM sale_products as sp
                              WHERE sp.product_id = products.id 
                              GROUP BY sp.product_id) as total_sale_amount"));

        //search query
        if (!empty($this->_product_id)) {
            $query->where('products.id', $this->_product_id);
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
        return  self::toBase()->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
