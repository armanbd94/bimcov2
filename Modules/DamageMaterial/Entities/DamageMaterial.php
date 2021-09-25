<?php

namespace Modules\DamageMaterial\Entities;

use App\Models\Unit;
use App\Models\User;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\Warehouse;

class DamageMaterial extends BaseModel
{
    protected $fillable = ['warehouse_id', 'material_id', 'qty', 'unit_id', 'net_unit_cost', 'total', 'damage_date','created_by', 'modified_by'];
    
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }
    
    public function material()
    {
        return $this->belongsTo(Material::class,'material_id','id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class,'unit_id','id');
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
    protected $_from_date; 
    protected $_to_date; 
    protected $_warehouse_id; 
    protected $_material_id; 


    //methods to set custom search property value
    public function setFromDate($from_date)
    {
        $this->_from_date = $from_date;
    }
    public function setToDate($to_date)
    {
        $this->_to_date = $to_date;
    }
    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }
    public function setMaterialID($material_id)
    {
        $this->_material_id = $material_id;
    }


    private function get_datatable_query()
    {
        
        if (permission('damage-material-bulk-delete')){
            $this->column_order = [null,'id','material_id','material_code','warehouse_id', 'unit_id', 'qty', 'net_unit_cost',
            'total', 'damage_date', null];
        }else{
            $this->column_order = ['id','material_id','material_code','warehouse_id', 'unit_id', 'qty','net_unit_cost',
            'total', 'damage_date', null];
        }
        
        $query = DB::table('damage_materials as dm')
        ->join('warehouses as w','dm.warehouse_id','=','w.id')
        ->join('materials as m','dm.material_id','=','m.id')
        ->join('units as u','dm.unit_id','=','u.id')
        ->selectRaw('dm.*,w.name as warehouse_name,m.material_name,m.material_code,u.unit_name,u.unit_code');
 
        //search query

        if (!empty($this->_from_date)) {
            $query->where('damage_date', '>=',$this->_from_date);
        }
        if (!empty($this->_to_date)) {
            $query->where('damage_date', '<=',$this->_to_date);
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('warehouse_id', $this->_warehouse_id);
        }
        if (!empty($this->_material_id)) {
            $query->where('material_id', $this->_material_id);
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
        return  DB::table('damage_materials')->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
