<?php

namespace Modules\Setting\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Cache;

class Warehouse extends BaseModel
{
    protected $fillable = ['name', 'phone', 'email', 'address', 'status', 'deletable', 'created_by', 'modified_by'];

    public function materials()
    {
        return $this->hasMany(\Modules\Material\Entities\Material::class,'warehouse_materials',
        'warehouse_id','material_id','id','id')->withTimeStamps()->withPivot('qty');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $name; 

    //methods to set custom search property value
    public function setName($name)
    {
        $this->name = $name;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('warehouse-bulk-delete')){
            $this->column_order = [null,'id','name','phone','email','address',null,null,'status',null];
        }else{
            $this->column_order = ['id','name','phone','email','address',null,null,'status',null];
        }
        
        $query = self::toBase();

        //search query
        if (!empty($this->name)) {
            $query->where('name', 'like', '%' . $this->name . '%');
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

    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const ALL_WAREHOUSES    = '_warehouses';
    protected const ACTIVE_WAREHOUSES    = '_active_warehouses';

    public static function allWarehouses(){
        return Cache::rememberForever(self::ALL_WAREHOUSES, function () {
            return self::toBase()->get();
        });
    }
    public static function activeWarehouses(){
        return Cache::rememberForever(self::ACTIVE_WAREHOUSES, function () {
            return self::toBase()->where('status',1)->get();
        });
    }


    public static function flushCache(){
        Cache::forget(self::ALL_WAREHOUSES);
        Cache::forget(self::ACTIVE_WAREHOUSES);
    }


    public static function boot(){
        parent::boot();

        static::updated(function () {
            self::flushCache();
        });

        static::created(function() {
            self::flushCache();
        });

        static::deleted(function() {
            self::flushCache();
        });
    }
    /***********************************
    * * *  Begin :: Cache Data * * *
    ************************************/
}
