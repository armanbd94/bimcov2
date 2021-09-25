<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
class TodaysCustomerReceipt extends BaseModel
{
    protected $table = 'transactions';
    protected $order = ['transactions.voucher_date' => 'desc'];
    protected $fillable = ['chart_of_account_id', 'voucher_no', 'voucher_type', 'voucher_date', 'description', 'debit', 
    'credit', 'posted', 'approve', 'created_by', 'modified_by'];

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['transaction.id','c.customer_id','transactions.description', 'transactions.credit',null];
        
        
        $query = self::select('transactions.*','coa.id as coa_id','coa.code','coa.name','coa.parent_name',
        'c.id as customer_id','c.name as customer_name','c.mobile')
        ->join('chart_of_accounts as coa','transactions.chart_of_account_id','=','coa.id')
        ->join('customers as c','coa.customer_id','c.id')
        ->where([
            ['transactions.credit','>',0],
            ['transactions.approve',1],
            ['transactions.voucher_date',date('Y-m-d')]
        ]);


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
        return self::select('transactions.*','coa.id as coa_id','coa.code','coa.name','coa.parent_name',
        'c.id as customer_id','c.name as customer_name','c.mobile')
        ->join('chart_of_accounts as coa','transactions.chart_of_account_id','=','coa.id')
        ->join('customers as c','coa.customer_id','c.id')
        ->where([
            ['transactions.credit','>',0],
            ['transactions.approve',1],
            ['transactions.voucher_date',date('Y-m-d')]
        ])->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
