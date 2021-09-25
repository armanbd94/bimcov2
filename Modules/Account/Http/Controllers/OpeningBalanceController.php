<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use App\Models\Warehouse;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Http\Requests\OpeningBalanceFormRequest;

class OpeningBalanceController extends BaseController
{
    
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('opening-balance-access')){
            $this->setPageData('Opening Balance','Opening Balance','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Opening Balance']]);
            $coas = ChartOfAccount::where('status',1)->orderBy('name','asc')->get();
            $voucher_no = 'OP-'.date('Ymd').rand(1,999);
            return view('account::opening-balance.index',compact('coas','voucher_no'));
        }else{
            return $this->access_blocked();
        }
    }

    public function store(OpeningBalanceFormRequest $request)
    {
        if($request->ajax()){
            if(permission('opening-balance-access')){
                DB::beginTransaction();
                try {
                    $data = array(
                        'chart_of_account_id' => $request->chart_of_account_id,
                        'voucher_no'          => $request->voucher_no,
                        'voucher_type'        => 'Opening',
                        'voucher_date'        => $request->voucher_date,
                        'description'         => $request->remarks,
                        'debit'               => $request->amount,
                        'credit'              => 0,
                        'is_opening'          => 1,
                        'posted'              => 1,
                        'approve'             => 1,
                        'created_by'          => auth()->user()->name,
                        'created_at'          => date('Y-m-d H:i:s')
                    );
                    $result = $this->model->create($data);
                    $output = $this->store_message($result, null);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
