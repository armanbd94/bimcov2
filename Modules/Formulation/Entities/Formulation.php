<?php

namespace Modules\Formulation\Entities;

use App\Models\Unit;
use App\Models\BaseModel;
use Modules\Product\Entities\Product;
use Modules\Material\Entities\Material;

class Formulation extends BaseModel
{
    protected $fillable = [ 'product_id', 'date', 'formulation_no', 'unit_id', 'total_fg_qty', 'total_material_qty', 
    'total_material_value', 'note', 'status', 'created_by', 'modified_by'];
    
    public function product()
    {
        return $this->belongsTo(Product::class); 
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class); 
    }
    public function materials()
    {
        return $this->belongsToMany(Material::class,'formulation_materials','formulation_id',
        'material_id','id','id')
        ->withPivot('id', 'unit_id', 'qty', 'rate', 'total')
        ->withTimeStamps(); 
    }

   /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_formulation_no; 
    protected $_start_date; 
    protected $_end_date;  
    protected $_product_id; 
    protected $_status; 

    //methods to set custom search property value
    public function setFormulationNo($formulation_no)
    {
        $this->_formulation_no = $formulation_no;
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



    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if(permission('forlumation-bulk-delete')){
            $this->column_order = ['id','id','date','product_id','formulation_no','unit_id','total_fg_qty','total_material_qty','total_material_value','status',null];
        }else{
            $this->column_order = ['id','date','product_id','formulation_no','unit_id','total_fg_qty','total_material_qty','total_material_value','status',null];
        }
        
        $query = self::with(['unit:id,unit_name','product:id,name,code']);

        //search query
        if (!empty($this->_formulation_no)) {
            $query->where('formulation_no',  $this->_formulation_no);
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
