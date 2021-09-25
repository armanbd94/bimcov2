<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use App\Models\Warehouse;
use Modules\Supplier\Entities\Supplier;

class TodaysPurchaseReport extends BaseModel
{
    protected $table = 'purchases';
    protected $fillable = [
        'invoice_no', 'warehouse_id', 'supplier_id', 'item', 'total_qty', 'total_discount', 'total_tax', 'total_labor_cost',
        'total_cost', 'order_tax_rate', 'order_tax', 'order_discount', 'shipping_cost', 'grand_total', 'paid_amount',
        'purchase_status', 'payment_status', 'status','status_change_by','status_change_date','document', 'note', 'purchase_date', 'created_by', 'modified_by'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['id','purchase_date','invoice_no','created_by',  'supplier_id', 'grand_total'];
        
        
        $query = self::with('supplier:id,name,mobile')
        ->where('purchase_date', date('Y-m-d'));

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
        return self::with('supplier:id,name,mobile')
        ->where('purchase_date', date('Y-m-d'))->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
