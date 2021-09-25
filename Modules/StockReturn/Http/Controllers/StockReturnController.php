<?php

namespace Modules\StockReturn\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\Request;
use Modules\Sale\Entities\Sale;
use Modules\Customer\Entities\Customer;
use App\Http\Controllers\BaseController;
use Modules\Purchase\Entities\Purchase;

class StockReturnController extends BaseController
{
    public function index()
    {
        if(permission('return-access')){
            $this->setPageData('Return','Return','fas fa-undo-alt',[['name' => 'Return']]);
            return view('stockreturn::form');
        }else{
            return $this->access_blocked();
        }
    }

    public function return_sale(Request $request)
    {
        if(permission('sale-return-access')){
            $sale = Sale::with('sale_products','customer','warehouse')->where('invoice_no',$request->get('invoice_no'))->first();

            if($sale){
                $this->setPageData('Sale Return','Sale Return','fas fa-undo-alt',[['name' => 'Sale Return']]);
                $data = [
                    'sale'=>$sale,
                ];
                return view('stockreturn::sale.edit',$data);
            }else{
                return redirect('return')->with(['status'=>'error','message'=>'No Invoice Data Found']);
            }
        }else{
            return $this->access_blocked();
        }
    }
    public function return_purchase(Request $request)
    {
        if(permission('purchase-return-access')){
            $purchase = Purchase::with('purchase_materials','supplier','warehouse')->where('invoice_no',$request->get('invoice_no'))->first();
            // dd($purchase);
            if($purchase){
                $this->setPageData('Purchase Return','Purchase Return','fas fa-undo-alt',[['name' => 'Purchase Return']]);
                $data = [
                    'purchase'=>$purchase,
                ];
                return view('stockreturn::purchase.edit',$data);
            }else{
                return redirect('return')->with(['status'=>'error','message'=>'No Invoice Data Found']);
            }
        }else{
            return $this->access_blocked();
        }
    }
    
}
