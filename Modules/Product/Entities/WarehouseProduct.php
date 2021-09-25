<?php

namespace Modules\Product\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Warehouse;

class WarehouseProduct extends BaseModel
{

    protected $table = 'warehouse_product';
    protected $fillable = ['warehouse_id', 'product_id', 'qty'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $order = ['p.id' => 'asc'];
    protected $_name; 
    protected $_warehouse_id; 

    //methods to set custom search property value
    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['p.id','w.name','p.name','p.code','p.category_id','p.unit_id','p.cost','wp.qty',null];
    
        $query = DB::table('warehouse_product as wp')
        ->selectRaw('wp.qty,w.name as warehouse_name,p.id,p.name,p.code,p.cost,c.name as category_name,u.unit_name')
        ->join('warehouses as w','wp.warehouse_id','=','w.id')
        ->join('products as p','wp.product_id','=','p.id')
        ->join('categories as c','p.category_id','=','c.id')
        ->join('units as u','p.unit_id','=','u.id');
        if($this->_warehouse_id != 0){
            $query->where('wp.warehouse_id',$this->_warehouse_id);
        }
        
        if (!empty($this->_name)) {
            $query->where('p.name', 'like', '%' . $this->_name . '%');
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
        return DB::table('warehouse_product as wp')
        ->selectRaw('wp.qty,w.name as warehouse_name,p.id,p.name,p.code,c.name')
        ->join('warehouses as w','wp.warehouse_id','=','w.id')
        ->join('products as p','wp.product_id','=','p.id')
        ->join('categories as c','p.category_id','=','c.id')
        ->join('units as u','p.unit_id','=','u.id')
        ->where('wp.warehouse_id',$this->_warehouse_id)->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
    
}
