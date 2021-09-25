<?php

namespace Modules\StockReturn\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Product\Entities\Product;
use Modules\Customer\Entities\Customer;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Product\Entities\ProductVariant;
use Modules\StockReturn\Entities\SaleReturn;
use Modules\Product\Entities\WarehouseProduct;
use Modules\StockReturn\Entities\SaleReturnProduct;
use Modules\StockReturn\Http\Requests\SaleReturnRequest;

class SaleReturnController extends BaseController
{
    public function __construct(SaleReturn $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('sale-return-access')){
            $this->setPageData('Sale Return','Sale Return','fas fa-file',[['name' => 'Sale Return']]);
            $data = [
                'customers'  => Customer::where('status',1)->get(),
            ];
            return view('stockreturn::sale.index',$data);
        }else{
            return $this->access_blocked();
        }

    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('sale-return-access')){

                if (!empty($request->return_no)) {
                    $this->model->setReturnNo($request->return_no);
                }
                if (!empty($request->invoice_no)) {
                    $this->model->setInvoiceNo($request->invoice_no);
                }
                if (!empty($request->from_date)) {
                    $this->model->setFromDate($request->from_date);
                }
                if (!empty($request->to_date)) {
                    $this->model->setToDate($request->to_date);
                }
                if (!empty($request->customer_id)) {
                    $this->model->setCustomerID($request->customer_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if (permission('sale-return-view')) {
                        $action .= ' <a class="dropdown-item view_data" href="'.route("sale.return.list.show",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }

                    if (permission('sale-return-delete')) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->return_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    $row = [];
                    if(permission('sale-return-bulk-delete')){
                        $row[] = row_checkbox($value->id);
                    }
                    $row[] = $no;
                    $row[] = $value->return_no;
                    $row[] = $value->invoice_no;
                    $row[] = $value->customer->name.($value->customer->mobile ? ' ( '.$value->customer->mobile.')' : '');
                    $row[] = date(config('settings.date_format'),strtotime($value->return_date));
                    $row[] = number_format($value->grand_total,2);
                    $row[] = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }   
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function store(SaleReturnRequest $request)
    {
        if($request->ajax()){
            if(permission('sale-return-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $sale_return_date = [
                        'return_no'        => 'SRINV-'.date('ymd').rand(1,999),
                        'invoice_no'       => $request->invoice_no,
                        'customer_id'      => $request->customer_id,
                        'total_price'      => $request->total_price,
                        'total_deduction'  => $request->total_deduction ? $request->total_deduction : null,
                        'tax_rate'         => $request->tax_rate ? $request->tax_rate : null,
                        'total_tax'        => $request->total_tax ? $request->total_tax : null,
                        'grand_total'      => $request->grand_total_price,
                        'reason'           => $request->reason,
                        'date'             => $request->sale_date,
                        'return_date'      => $request->return_date,
                        'created_by'       => Auth::user()->name
                    ];

                    $sale_return  = $this->model->create($sale_return_date);
                    //purchase products
                    $products = [];
                    if($request->has('products'))
                    {
                        foreach ($request->products as $key => $value) {
                            if($value['return'] == 1){
                                $unit = Unit::where('unit_name',$value['unit'])->first();
                                if($unit->operator == '*'){
                                    $qty = $value['return_qty'] * $unit->operation_value;
                                }else{
                                    $qty = $value['return_qty'] / $unit->operation_value;
                                }

                                $products[] = [
                                    'sale_return_id'    => $sale_return->id,
                                    'invoice_no'         => $request->invoice_no,
                                    'product_id'         => $value['id'],
                                    'return_qty'         => $value['return_qty'],
                                    'unit_id'            => $unit ? $unit->id : null,
                                    'product_rate'       => $value['net_unit_price'],
                                    'deduction_rate'     => $value['deduction_rate'] ? $value['deduction_rate'] : null,
                                    'deduction_amount'   => $value['deduction_amount'] ? $value['deduction_amount'] : null,
                                    'total'              => $value['total']
                                ];

                                $product = Product::find($value['id']);
                                if($product){
                                    $product->qty += $qty;
                                    $product->update();
                                }
                                $warehouse_product = WarehouseProduct::where([
                                    'warehouse_id'=>$request->warehouse_id,
                                    'product_id'=>$value['id'],
                                    ])->first();
                                if($warehouse_product){
                                    $warehouse_product->qty += $qty;
                                    $warehouse_product->update();
                                }
                               
                            }
                            
                        }
                        if(count($products) > 0)
                        {
                            SaleReturnProduct::insert($products);
                        }
                    }

                    $customer = Customer::with('coa')->find($request->customer_id);
                    $customer_credit = array(
                        'chart_of_account_id' => $customer->coa->id,
                        'voucher_no'          => $request->invoice_no,
                        'voucher_type'        => 'Return',
                        'voucher_date'        => $request->return_date,
                        'description'         => 'Customer '.$customer->name.' credit for Product Return Invoice NO- ' . $request->invoice_no,
                        'debit'               => 0,
                        'credit'              => $request->grand_total_price,
                        'posted'              => 1,
                        'approve'             => 1,
                        'created_by'          => auth()->user()->name,
                        'created_at'          => date('Y-m-d H:i:s')
                    );
                    Transaction::create($customer_credit);
                    $output  = $this->store_message($sale_return, null);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
                return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function show(int $id)
    {
        if(permission('sale-return-view')){
            $this->setPageData('Sale Return Details','Sale Return Details','fas fa-file',[['name' => 'Sale Return Details']]);
            $sale = $this->model->with('return_products','customer')->find($id);
            return view('stockreturn::sale.details',compact('sale'));
        }else{
            return $this->access_blocked();
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('sale-return-delete'))
            {
                DB::beginTransaction();
                try {
    
                    $saleData = $this->model->with('sale','return_products')->find($request->id);
                    $invoice_no = $saleData->invoice_no;
    
                    if(!$saleData->return_products->isEmpty())
                    {
                        
                        foreach ($saleData->return_products as  $return_product) {
                            $return_qty = $return_product->return_qty;
                            $sale_unit = Unit::find($return_product->unit_id);
                            if($sale_unit->operator == '*'){
                                $return_qty = $return_qty * $sale_unit->operation_value;
                            }else{
                                $return_qty = $return_qty / $sale_unit->operation_value;
                            }
                            $product_data = Product::find($return_product->product_id);
                            if($product_data){
                                $product_data->qty -= $return_qty;
                                $product_data->update();
                            }
                            $warehouse_product = WarehouseProduct::where([
                                'warehouse_id'=> $saleData->sale->warehouse_id,
                                'product_id'=> $return_product->product_id,
                                ])->first();
                            if($warehouse_product){
                                $warehouse_product->qty -= $return_qty;
                                $warehouse_product->update();
                            }
                        }
                        $saleData->return_products()->delete();
                    }
                    Transaction::where(['voucher_no'=>$invoice_no,'voucher_type'=>'Return'])->delete();
    
                    $result = $saleData->delete();
                    if($result)
                    {
                        $output = ['status' => 'success','message' => 'Data has been deleted successfully'];
                    }else{
                        $output = ['status' => 'error','message' => 'Failed to delete data'];
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
                return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('sale-return-bulk-delete')){
                
                    DB::beginTransaction();
                    try {
                        foreach ($request->ids as $id) {
                            $saleData = $this->model->with('sale','return_products')->find($id);
                            $invoice_no = $saleData->invoice_no;
            
                            if(!$saleData->return_products->isEmpty())
                            {
                                
                                foreach ($saleData->return_products as  $return_product) {
                                    $return_qty = $return_product->return_qty;
                                    $sale_unit = Unit::find($return_product->unit_id);
                                    if($sale_unit->operator == '*'){
                                        $return_qty = $return_qty * $sale_unit->operation_value;
                                    }else{
                                        $return_qty = $return_qty / $sale_unit->operation_value;
                                    }
                                    $product_data = Product::find($return_product->product_id);
                                    if($product_data){
                                        $product_data->qty -= $return_qty;
                                        $product_data->update();
                                    }
                                    $warehouse_product = WarehouseProduct::where([
                                        'warehouse_id'=> $saleData->sale->warehouse_id,
                                        'product_id'=> $return_product->product_id,
                                        ])->first();
                                    if($warehouse_product){
                                        $warehouse_product->qty -= $return_qty;
                                        $warehouse_product->update();
                                    }
                                }
                                $saleData->return_products()->delete();
                            }
                            Transaction::where(['voucher_no'=>$invoice_no,'voucher_type'=>'Return'])->delete();
            
                            $result = $saleData->delete();
                            if($result)
                            {
                                $output = ['status' => 'success','message' => 'Data has been deleted successfully'];
                            }else{
                                $output = ['status' => 'error','message' => 'Failed to delete data'];
                            }
                        }
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $output = ['status'=>'error','message'=>$e->getMessage()];
                    }
                    return response()->json($output);
                
            }else{
                $output = $this->access_blocked();
            }
            return response()->json($output);
        }else{
            return response()->json($this->access_blocked());
        }
    }
}
