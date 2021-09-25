<?php

namespace Modules\StockReturn\Entities;

use App\Models\BaseModel;
use Modules\Sale\Entities\Sale;
use Illuminate\Database\Eloquent\Model;
use Modules\Customer\Entities\Customer;
use Modules\StockReturn\Entities\SaleReturnProduct;

class SaleReturn extends BaseModel
{
    protected $fillable = ['return_no', 'invoice_no', 'customer_id', 'total_price', 'total_deduction', 'tax_rate',
     'total_tax', 'grand_total', 'reason', 'date', 'return_date', 'created_by', 'modified_by'];
    
     public function sale()
     {
         return $this->belongsTo(Sale::class,'invoice_no','invoice_no');
     }
     public function customer()
     {
         return $this->belongsTo(Customer::class,'customer_id','id')->withDefault(['name'=>'','company_name'=>'','mobile'=>'']);
     }

     public function return_products()
     {
        return $this->hasMany(SaleReturnProduct::class,'sale_return_id','id'); 
     }
      /******************************************
      * * * Begin :: Custom Datatable Code * * *
     *******************************************/
     //custom search column property
     protected $_return_no; 
     protected $_invoice_no; 
     protected $_from_date; 
     protected $_to_date; 
     protected $_customer_id; 
 
     //methods to set custom search property value
     public function setReturnNo($return_no)
     {
         $this->_return_no = $return_no;
     }
     public function setInvoiceNo($invoice_no)
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
     public function setCustomerID($customer_id)
     {
         $this->_customer_id = $customer_id;
     }
 
 
     private function get_datatable_query()
     {
         //set column sorting index table column name wise (should match with frontend table header)
         if(permission('sale-return-bulk-delete')){
             $this->column_order = ['id','id','return_no','invoice_no', null, 'return_date', 'grand_total', null];
         
         }else{
             $this->column_order = ['id','return_no','invoice_no', null, 'return_date', 'grand_total', null];
         }
         
         $query = self::with('customer');
 
         //search query
         if (!empty($this->_return_no)) {
             $query->where('return_no', 'like', '%' . $this->_return_no . '%');
         }
         if (!empty($this->_invoice_no)) {
             $query->where('invoice_no', 'like', '%' . $this->_invoice_no . '%');
         }
 
         if (!empty($this->_from_date)) {
             $query->where('return_date', '>=',$this->_from_date);
         }
         if (!empty($this->_to_date)) {
             $query->where('return_date', '<=',$this->_to_date);
         }
 
         if (!empty($this->_customer_id)) {
             $query->where('customer_id', $this->_customer_id);
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
