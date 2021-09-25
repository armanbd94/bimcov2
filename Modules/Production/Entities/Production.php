<?php

namespace Modules\Production\Entities;

use App\Models\Unit;
use App\Models\User;
use App\Models\BaseModel;
use Modules\Product\Entities\Product;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\Warehouse;
use Modules\Formulation\Entities\Formulation;

class Production extends BaseModel
{
    protected $fillable = ['reference_no','batch_no', 'mfg_date', 'exp_date', 'date', 'product_id',
    'formulation_id', 'unit_id', 'total_fg_qty', 'total_cost', 'extra_cost', 'total_net_cost',
    'per_unit_cost', 'pwarehouse_id', 'mwarehouse_id', 'note', 'status',
    'production_status', 'created_by', 'modified_by'];

    public function product()
    {
        return $this->belongsTo(Product::class); 
    }
    public function pwarehouse()
    {
        return $this->belongsTo(Warehouse::class,'pwarehouse_id','id'); 
    }
    public function mwarehouse()
    {
        return $this->belongsTo(Warehouse::class,'mwarehouse_id','id'); 
    }
    public function formulation()
    {
        return $this->belongsTo(Formulation::class,'formulation_id','id'); 
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class); 
    }
    public function materials()
    {
        return $this->belongsToMany(Material::class,'production_materials','production_id',
        'material_id','id','id')
        ->withPivot('id', 'unit_id', 'qty', 'rate', 'total')
        ->withTimeStamps(); 
    }
    public function packaging_materials()
    {
        return $this->belongsToMany(Material::class,'production_packaging_materials','production_id',
        'material_id','id','id')
        ->withPivot('id', 'prefix', 'unit_id', 'qty', 'rate', 'total')
        ->withTimeStamps(); 
    }
    public function creator()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }
    public function modifier()
    {
        return $this->belongsTo(User::class,'modified_by','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_batch_no; 
    protected $_start_date; 
    protected $_end_date;  
    protected $_product_id; 
    protected $_status; 
    protected $_production_status; 

    //methods to set custom search property value
    public function setBatchNo($batch_no)
    {
        $this->_batch_no = $batch_no;
    }

    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }

    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }

    public function setProductID($product_id)
    {
        $this->_product_id = $product_id;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }
    public function setProductionStatus($production_status)
    {
        $this->_production_status = $production_status;
    }



    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if(permission('product-mixing-bulk-delete')){
            $this->column_order = ['id','id','batch_no','date','product_id','formulation_id','formulation_id','unit_id','total_fg_qty','total_cost', 'extra_cost', 'total_net_cost','per_unit_cost','status','production_status','reference_no'
            ,null];
        }else{
            $this->column_order = ['id','batch_no','date','product_id','formulation_id','formulation_id','unit_id','total_fg_qty','total_cost', 'extra_cost', 'total_net_cost','per_unit_cost','status','production_status','reference_no',null];
        }
        
        $query = self::with(['unit:id,unit_name','product:id,name,code','formulation:id,formulation_no']);

        //search query
        if (!empty($this->_batch_no)) {
            $query->where('batch_no',  $this->_batch_no);
        }

        if (!empty($this->_start_date)) {
            $query->where('date', '>=',$this->_start_date);
        }

        if (!empty($this->_end_date)) {
            $query->where('date', '<=',$this->_end_date);
        }

        if (!empty($this->_product_id)) {
            $query->where('product_id', $this->_product_id);
        }

        if (!empty($this->_status)) {
            $query->where('status', $this->_status);
        }
        if (!empty($this->_production_status)) {
            $query->where('production_status', $this->_production_status);
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
        return self::toBase()->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
    
}
