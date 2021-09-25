<?php

namespace Modules\Account\Http\Controllers;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Account\Http\Requests\CashAdjustmentFormRequest;

class CashAdjustmentController extends BaseController
{
    protected const VOUCHER_PREFIX = 'CHV';
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('cash-adjustment-access')){
            $this->setPageData('Cash Adjustment','Cash Adjustment','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Cash Adjustment']]);
            $voucher_no = self::VOUCHER_PREFIX.'-'.date('Ymd').rand(1,999);
            return view('account::cash-adjustment.index',compact('voucher_no'));
        }else{
            return $this->access_blocked();
        }
    }

    public function store(CashAdjustmentFormRequest $request)
    {
        if($request->ajax()){
            if(permission('cash-adjustment-access')){
                DB::beginTransaction();
                try {
                    $data = array(
                        'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('cash_in_hand'))->value('id'),
                        'voucher_no'          => $request->voucher_no,
                        'voucher_type'        => 'ADJUSTMENT',
                        'voucher_date'        => $request->voucher_date,
                        'description'         => $request->remarks,
                        'debit'               => ($request->type == 'debit') ? $request->amount : 0,
                        'credit'              => ($request->type == 'credit') ? $request->amount : 0,
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
