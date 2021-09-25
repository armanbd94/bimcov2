<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Http\Requests\CreditVoucherFormRequest;

class CreditVoucherController extends BaseController
{
    private const VOUCHER_PREFIX = 'CV';
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('credit-voucher-access')){
            $this->setPageData('Credit Voucher','Credit Voucher','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Credit Voucher']]);
            $data = [
            'voucher_no'             => self::VOUCHER_PREFIX.'-'.date('Ymd').rand(1,999),
            'transactional_accounts' => ChartOfAccount::where(['status'=>1,'transaction'=>1])->orderBy('id','asc')->get(),
            'debit_accounts'        => ChartOfAccount::where(['status'=>1,'transaction'=>1])
                                    ->where('code','like','1020101')
                                    ->orWhere('code','like','10201020%')
                                    ->orWhere('code','like','10201030%')
                                    ->orderBy('id','asc')->get()
            ];
            return view('account::credit-voucher.create',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function store(CreditVoucherFormRequest $request)
    {
        if($request->ajax()){
            if(permission('credit-voucher-access')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $credit_voucher_transaction = [];
                    if ($request->has('credit_account')) {
                        foreach ($request->credit_account as $key => $value) {
                            //Credit Insert
                            $credit_voucher_transaction[] = array(
                                'chart_of_account_id' => $value['id'],
                                'voucher_no'          => $request->voucher_no,
                                'voucher_type'        => self::VOUCHER_PREFIX,
                                'voucher_date'        => $request->voucher_date,
                                'description'         => $request->remarks,
                                'debit'               => 0,
                                'credit'              => $value['amount'],
                                'posted'              => 1,
                                'approve'             => 2,
                                'created_by'          => auth()->user()->name,
                                'created_at'          => date('Y-m-d H:i:s')
                            );

                            //Cash In Insert
                            $debit_account = ChartOfAccount::find($request->debit_account_id);
                            $credit_voucher_transaction[] = array(
                                'chart_of_account_id' => $request->debit_account_id,
                                'voucher_no'          => $request->voucher_no,
                                'voucher_type'        => self::VOUCHER_PREFIX,
                                'voucher_date'        => $request->voucher_date,
                                'description'         => 'Credit Voucher From '.($debit_account ? $debit_account->name : ''),
                                'debit'               => $value['amount'],
                                'credit'              => 0,
                                'posted'              => 1,
                                'approve'             => 2,
                                'created_by'          => auth()->user()->name,
                                'created_at'          => date('Y-m-d H:i:s')
                            );
                        }
                    }

                    // dd($credit_voucher_transaction);
                    $result = $this->model->insert($credit_voucher_transaction);
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
    public function update(CreditVoucherFormRequest $request)
    {
        if($request->ajax()){
            if(permission('edit-voucher')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    Transaction::where('voucher_no',$request->voucher_no)->delete();
                    $credit_voucher_transaction = [];
                    if ($request->has('credit_account')) {
                        foreach ($request->credit_account as $key => $value) {
                            //Credit Insert
                            $credit_voucher_transaction[] = array(
                                'chart_of_account_id' => $value['id'],
                                'voucher_no'          => $request->voucher_no,
                                'voucher_type'        => self::VOUCHER_PREFIX,
                                'voucher_date'        => $request->voucher_date,
                                'description'         => $request->remarks,
                                'debit'               => 0,
                                'credit'              => $value['amount'],
                                'posted'              => 1,
                                'approve'             => 2,
                                'created_by'          => auth()->user()->name,
                                'created_at'          => date('Y-m-d H:i:s')
                            );

                            //Cash In Insert
                            $debit_account = ChartOfAccount::find($request->debit_account_id);
                            $credit_voucher_transaction[] = array(
                                'chart_of_account_id' => $request->debit_account_id,
                                'voucher_no'          => $request->voucher_no,
                                'voucher_type'        => self::VOUCHER_PREFIX,
                                'voucher_date'        => $request->voucher_date,
                                'description'         => 'Credit Voucher From '.($debit_account ? $debit_account->name : ''),
                                'debit'               => $value['amount'],
                                'credit'              => 0,
                                'posted'              => 1,
                                'approve'             => 2,
                                'created_by'          => auth()->user()->name,
                                'created_at'          => date('Y-m-d H:i:s')
                            );
                        }
                    }

                    // dd($credit_voucher_transaction);
                    $result = $this->model->insert($credit_voucher_transaction);
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
