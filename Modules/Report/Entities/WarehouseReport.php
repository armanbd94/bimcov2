<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class WarehouseReport extends BaseModel
{
    protected $table = 'warehouse_finish_goods';
    protected $fillable = ['warehouse_id','product_id','finish_goods_id','qty','rack_no'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }

    public function finish_goods()
    {
        return $this->belongsTo(FinishGood::class,'finish_goods_id','id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $order = ['wfg.product_id' => 'asc'];
    protected $_finish_goods_id; 
    protected $_warehouse_id;

    //methods to set custom search property value
    public function setFinishGoodsID($finish_goods_id)
    {
        $this->_finish_goods_id = $finish_goods_id;
    }
    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        $this->column_order = ['wfg.id','wfg.product_id','fg.size_id','fg.code','p.type','fg.unit_id','fg.qty','fg.alert_qty'];
        
        
        $query = DB::table('warehouse_finish_goods as wfg')
        ->selectRaw('w.name as warehouse_name,p.product_name,p.type,fg.code,
        wfg.qty,fg.alert_qty,s.size,su.unit_name as size_unit,u.unit_name as stock_unit')
        ->leftjoin('warehouses as w','wfg.warehouse_id','=','w.id')
        ->leftjoin('products as p','wfg.product_id','=','p.id')
        ->leftjoin('finish_goods as fg','wfg.finish_goods_id','=','fg.id')
        ->leftjoin('sizes as s','fg.size_id','=','s.id')
        ->leftjoin('units as su','s.unit_id','=','su.id')
        ->leftjoin('units as u','fg.unit_id','=','u.id')
        ->where('wfg.warehouse_id', $this->_warehouse_id);
        //search query
        if (!empty($this->_finish_goods_id)) {
            $query->where('wfg.finish_goods_id', $this->_finish_goods_id);
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
        $query = DB::table('warehouse_finish_goods as wfg')
        ->selectRaw('w.name as warehouse_name,p.product_name,p.type,fg.code,
        fg.qty,fg.opening_stock_qty,fg.alert_qty,s.size,su.unit_name as size_unit,u.unit_name as stock_unit')
        ->leftjoin('warehouses as w','wfg.warehouse_id','=','w.id')
        ->leftjoin('products as p','wfg.product_id','=','p.id')
        ->leftjoin('finish_goods as fg','wfg.finish_goods_id','=','fg.id')
        ->leftjoin('sizes as s','fg.size_id','=','s.id')
        ->leftjoin('units as su','s.unit_id','=','su.id')
        ->leftjoin('units as u','fg.unit_id','=','u.id')
        ->where('wfg.warehouse_id', $this->_warehouse_id);
        //search query

        return $query->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
