<?php

namespace Modules\Product\Entities;

use App\Models\Tax;
use App\Models\Unit;
use App\Models\Category;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\Warehouse;
use Modules\Production\Entities\Production;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends BaseModel
{
    use HasFactory;

    protected $fillable = [ 'name', 'category_id', 'code', 'unit_id', 'sale_unit_id', 'cost', 'price', 'qty', 'alert_quantity', 'image', 
    'tax_id', 'tax_method', 'status', 'has_opening_stock', 'opening_stock_qty', 'opening_cost','opening_warehouse_id', 'description', 'created_by', 'modified_by'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class,'unit_id','id');
    }

    public function sale_unit()
    {
        return $this->belongsTo(Unit::class,'sale_unit_id','id');
    }
    public function tax()
    {
        return $this->belongsTo(Tax::class)->withDefault(['name'=>'No Tax','rate' => 0]);
    }
    public function opening_warehouse()
    {
        return $this->belongsTo(Warehouse::class,'opening_warehouse_id','id')->withDefault(['name' => '']);
    }

    public function total_production_qty()
    {
        return $this->hasMany(Production::class)
        ->where('production_status',2);
    }
    public function total_production_cost()
    {
        return $this->hasMany(Production::class,'product_id','id')
        ->where('production_status',2);
    }

    // public function product_material(){
    //     return $this->belongsToMany(Material::class,'product_material','product_id','material_id','id','id')
    //                 ->withTimestamps();
    // }
    
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_name; 
    protected $_category_id; 
    protected $_status; 

    //methods to set custom search property value
    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setCategoryID($category_id)
    {
        $this->_category_id = $category_id;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('product-bulk-delete')){
            $this->column_order = [null,'id','id','name','code','category_id','unit_id','sale_unit_id','cost','price','qty', 'opening_stock_qty','tax_id','alert_quantity','tax_method','status',null];
        }else{
            $this->column_order = ['id','id','name','code','category_id','unit_id','sale_unit_id','cost','price','qty', 'opening_stock_qty','tax_id','tax_method','alert_quantity', 'status',null];
        }
        
        $query = self::with('category:id,name','tax:id,name,rate','unit:id,unit_name','sale_unit:id,unit_name');

        //search query
        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
        }

        if (!empty($this->_category_id)) {
            $query->where('category_id', $this->_category_id);
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
