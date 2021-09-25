<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Modules\Account\Entities\Transaction;
use Modules\Setting\Entities\CustomerGroup;
use Modules\Account\Entities\ChartOfAccount;

class Customer extends BaseModel
{
    protected $fillable = [ 'name', 'company_name', 'mobile', 'email', 'customer_group_id','city', 'zipcode', 
    'address', 'status', 'created_by', 'modified_by'];

    public function customer_group()
    {
        return $this->belongsTo(CustomerGroup::class,'customer_group_id','id');
    }

    public function coa(){
        return $this->hasOne(ChartOfAccount::class,'customer_id','id');
    }
    
    public function previous_balance()
    {
        return $this->hasOneThrough(Transaction::class,ChartOfAccount::class,'customer_id','chart_of_account_id','id','id')
        ->where('voucher_type','PR Balance')->withDefault(['debit' => '']);
    }

    public function customer_balance(int $id)
    {
        $data = DB::table('customers as c')
            ->selectRaw('c.id,b.id as coaid,b.code,((select ifnull(sum(debit),0) from transactions where chart_of_account_id= b.id AND approve = 1)-(select ifnull(sum(credit),0) from transactions where chart_of_account_id= b.id AND approve = 1)) as balance')
            ->leftjoin('chart_of_accounts as b', 'c.id', '=', 'b.customer_id')
            ->where('c.id',$id)->first();
        $balance = 0;
        if($data)
        {
            $balance = $data->balance ? $data->balance : 0;
        }
        return number_format($balance,2,'.',',');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_name; 
    protected $_company_name; 
    protected $_mobile; 
    protected $_email; 
    protected $_customer_group_id; 
    protected $_status; 

    //methods to set custom search property value
    public function setName($name)
    {
        $this->_name = $name;
    }
    public function setCompanyName($company_name)
    {
        $this->_company_name = $company_name;
    }
    public function setMobile($mobile)
    {
        $this->_mobile = $mobile;
    }
    public function setEmail($email)
    {
        $this->_email = $email;
    }
    public function setCustomerGroupID($customer_group_id)
    {
        $this->_customer_group_id = $customer_group_id;
    }
    public function setStatus($status)
    {
        $this->_status = $status;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('customer-bulk-delete')){
            $this->column_order = [null,'id','name', 'company_name', 'mobile', 'customer_group_id','email','city', 'zipcode','address','status', null, null];
        }else{
            $this->column_order = ['id','name', 'company_name', 'mobile', 'customer_group_id','email','city', 'zipcode','address','status',null,null];
        }
        
        $query = self::with('customer_group');

        //search query
        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
        }
        if (!empty($this->_company_name)) {
            $query->where('company_name', 'like', '%' . $this->_company_name . '%');
        }
        if (!empty($this->_mobile)) {
            $query->where('mobile', 'like', '%' . $this->_mobile . '%');
        }
        if (!empty($this->_email)) {
            $query->where('email', 'like', '%' . $this->_email . '%');
        }
        if (!empty($this->_customer_group_id)) {
            $query->where('customer_group_id',  $this->_customer_group_id);
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

    /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/
    public function scopeActive($query)
    {
        return $query->where(['status'=>1]);
    }

    public function scopeInactive($query)
    {
        return $query->where(['status'=>2]);
    }
    /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/

    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const ALL_CUSTOMERS    = '_customers';
    protected const ACTIVE_CUSTOMERS = '_active_customers';

    public static function allCustomers(){
        return Cache::rememberForever(self::ALL_CUSTOMERS, function () {
            return self::toBase()->orderBy('name','asc')->get();
        });
    }

    public static function activeCustomers(){
        return Cache::rememberForever(self::ACTIVE_CUSTOMERS, function () {
            return self::active()->orderBy('name','asc')->get();
        });
    }


    public static function flushCache(){
        Cache::forget(self::ALL_CUSTOMERS);
        Cache::forget(self::ACTIVE_CUSTOMERS);
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
