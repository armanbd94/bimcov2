<?php

namespace Modules\Purchase\Entities;

use App\Models\User;
use App\Models\BaseModel;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\Warehouse;
use Modules\Supplier\Entities\Supplier;
use Modules\Purchase\Entities\PurchasePayment;

class Purchase extends BaseModel
{
    protected $fillable = [
        'invoice_no', 'warehouse_id', 'supplier_id', 'item', 'total_qty', 'total_discount', 'total_tax', 'total_labor_cost',
        'total_cost', 'order_tax_rate', 'order_tax', 'order_discount', 'shipping_cost', 'grand_total', 'paid_amount',
        'purchase_status', 'payment_status', 'note', 'purchase_date', 'created_by', 'modified_by'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function  purchase_materials()
    {
        return $this->belongsToMany(Material::class,'purchase_materials','purchase_id','material_id','id','id')
        ->withPivot('qty', 'received', 'purchase_unit_id', 'net_unit_cost', 'discount', 'tax_rate', 'tax', 'total')
        ->withTimeStamps(); 
    }

    public function purchase_payments()
    {
        return $this->hasMany(PurchasePayment::class);
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
    protected $_invoice_no; 
    protected $_from_date; 
    protected $_to_date; 
    protected $_warehouse_id; 
    protected $_supplier_id; 
    protected $_purchase_status; 
    protected $_payment_status; 

    //methods to set custom search property value
    public function setMemoNo($invoice_no)
    {
        $this->_invoice_no = $invoice_no;
    }

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
    public function setSupplierID($supplier_id)
    {
        $this->_supplier_id = $supplier_id;
    }
    public function setPurchaseStatus($purchase_status)
    {
        $this->_purchase_status = $purchase_status;
    }
    public function setPaymentStatus($payment_status)
    {
        $this->_payment_status = $payment_status;
    }


    private function get_datatable_query()
    {
        
        if (permission('purchase-bulk-delete')){
            $this->column_order = [null,'id','invoice_no','warehouse_id','supplier_id', 'item', 'total_cost',
            'shipping_cost', 'grand_total', 'paid_amount', 'due_amount', 'purchase_date', 'purchase_status', 'payment_type','payment_status', null];
        }else{
            $this->column_order = ['id','invoice_no','warehouse_id','supplier_id', 'item', 'total_cost',
            'shipping_cost', 'grand_total', 'paid_amount', 'due_amount', 'purchase_date', 'purchase_status', 'payment_type','payment_status', null];
        }
        
        $query = self::with(['supplier:id,name,company_name,mobile','warehouse:id,name']);

        //search query
        if (!empty($this->_invoice_no)) {
            $query->where('invoice_no', 'like', '%' . $this->_invoice_no . '%');
        }

        if (!empty($this->_from_date)) {
            $query->where('purchase_date', '>=',$this->_from_date);
        }
        if (!empty($this->_to_date)) {
            $query->where('purchase_date', '<=',$this->_to_date);
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('warehouse_id', $this->_warehouse_id);
        }
        if (!empty($this->_supplier_id)) {
            $query->where('supplier_id', $this->_supplier_id);
        }
        if (!empty($this->_purchase_status)) {
            $query->where('purchase_status', $this->_purchase_status);
        }
        if (!empty($this->_payment_status)) {
            $query->where('payment_status', $this->_payment_status);
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
