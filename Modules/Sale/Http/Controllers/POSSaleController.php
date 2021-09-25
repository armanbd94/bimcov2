<?php

namespace Modules\Sale\Http\Controllers;

use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Entities\POSSale;
use Illuminate\Support\Facades\Auth;
use Modules\Product\Entities\Product;
use Illuminate\Support\Facades\Session;
use Modules\Customer\Entities\Customer;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Product\Entities\WarehouseProduct;
use Modules\Sale\Http\Requests\POSSaleRequest;

class POSSaleController extends BaseController
{
    public function __construct(POSSale $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('pos-sale-access')){
            $this->setPageData('POS Sale Manage','POS Sale Manage','fab fa-opencart',[['name' => 'POS Sale Manage']]);
            $data = [
                'warehouses' => Warehouse::where('status',1)->get(),
            ];
            return view('sale::pos-sale.index',$data);
        }else{
            return $this->access_blocked();
        }

    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('pos-sale-access')){
                if (!empty($request->invoice_no)) {
                    $this->model->setInvoiceNo($request->invoice_no);
                }
                if (!empty($request->from_date)) {
                    $this->model->setFromDate($request->from_date);
                }
                if (!empty($request->to_date)) {
                    $this->model->setToDate($request->to_date);
                }

                if (!empty($request->warehouse_id)) {
                    $this->model->setWarehouseID($request->warehouse_id);
                }


                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if (permission('pos-sale-edit')) {
                        $action .= ' <a class="dropdown-item" href="'.route("pos.sale.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if (permission('pos-sale-view')) {
                        $action .= ' <a class="dropdown-item view_data" href="'.route("pos.sale.show",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }

                    if (permission('pos-sale-delete')) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->invoice_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    $row = [];
                    if(permission('pos-sale-bulk-delete')){
                        $row[] = row_checkbox($value->id);
                    }
                    $row[] = $no;
                    $row[] = $value->invoice_no;
                    $row[] = $value->warehouse->name;
                    $row[] = $value->customer->name;
                    $row[] = $value->item;
                    $row[] = number_format($value->total_price,2);
                    $row[] = number_format($value->grand_total,2);
                    $row[] = number_format($value->paid_amount,2);
                    $row[] = number_format($value->due_amount,2);
                    $row[] = date(config('settings.date_format'),strtotime($value->sale_date));
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

    public function create()
    {
        if(permission('pos-sale-add')){
            $this->setPageData('Add Sale','Add Sale','fas fa-shopping-cart',[['name' => 'Add Sale']]);
            $sale = $this->model->select('invoice_no')->orderBy('invoice_no','desc')->first();
            $data = [
                'warehouses'  => Warehouse::where('status',1)->get(),
                'taxes'       => Tax::activeTaxes(),
                'invoice_no'  => $sale ? 'IN-'.(explode('IN-',$sale->invoice_no)[1] + 1) : 'IN-1001'
            ];
            return view('sale::pos-sale.create',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function store(POSSaleRequest $request)
    {
        if($request->ajax()){
            if(permission('pos-sale-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $sale_data = [
                        'invoice_no'       => $request->invoice_no,
                        'warehouse_id'     => $request->warehouse_id,
                        'customer_id'      => 1,
                        'customer_name'    => $request->customer_name,
                        'item'             => $request->item,
                        'total_qty'        => $request->total_qty,
                        'total_discount'   => $request->total_discount,
                        'total_tax'        => $request->total_tax,
                        'total_price'      => $request->total_price,
                        'order_tax_rate'   => $request->order_tax_rate,
                        'order_tax'        => $request->order_tax,
                        'order_discount'   => $request->order_discount ? $request->order_discount : 0,
                        'shipping_cost'    => $request->shipping_cost ? $request->shipping_cost : 0,
                        'total_labor_cost' =>  0,
                        'grand_total'      => $request->grand_total,
                        'previous_due'     => 0,
                        'paid_amount'      =>  $request->grand_total,
                        'due_amount'       => 0,
                        'payment_status'   => 1,
                        'payment_method'   => 1,
                        'account_id'       => 23,
                        'type'             =>2,
                        'sale_date'        => $request->sale_date,
                        'created_by'       => Auth::user()->id
                    ];

                    $payment_data = [
                        'payment_method' => 1,
                        'account_id'     => 23,
                        'paid_amount'    =>  $request->grand_total,
                    ];

                    //purchase products
                    $products = [];
                    $direct_cost = [];
                    if($request->has('products'))
                    {
                        foreach ($request->products as $key => $value) {
                            $unit = Unit::where('unit_name',$value['unit'])->first();
                            if($unit->operator == '*'){
                                $qty = $value['qty'] * $unit->operation_value;
                            }else{
                                $qty = $value['qty'] / $unit->operation_value;
                            }

                            $products[$value['id']] = [
                                'qty'              => $value['qty'],
                                'sale_unit_id'     => $unit ? $unit->id : null,
                                'net_unit_price'   => $value['net_unit_price'],
                                'discount'         => $value['discount'],
                                'tax_rate'         => $value['tax_rate'],
                                'tax'              => $value['tax'],
                                'total'            => $value['subtotal']
                            ];

                            $warehouse_product = WarehouseProduct::where([
                                ['warehouse_id',$request->warehouse_id],['product_id',$value['id']],['qty','>',0]])->first();
                            if($warehouse_product){
                                $warehouse_product->qty -= $qty;
                                $warehouse_product->save();
                            }

                            $product = Product::where([['id',$value['id']],['qty','>',0]])->first();
                            if($product)
                            {
                                $direct_cost[] = $qty * ($product ? $product->cost : 0);
                                $product->qty -= $qty;
                                $product->update();
                            }
                        }
                    }
                    $sum_direct_cost = array_sum($direct_cost);

                    $total_tax = ($request->total_tax ? $request->total_tax : 0) + ($request->order_tax ? $request->order_tax : 0);
                    $result  = $this->model->create($sale_data);
                    $sale = $this->model->with('sale_products')->find($result->id);

                    if(count($products) > 0){
                        $sale->sale_products()->sync($products);
                    }
                    $customer = Customer::with('coa')->find(1);

                    $data = $this->sale_balance_add($result->id,$request->invoice_no,$request->grand_total,$total_tax,$sum_direct_cost,$customer->coa->id,$customer->name,$request->sale_date,$payment_data,$request->warehouse_id);

                    $output  = $this->store_message($result, $request->sale_id);
                    $output['sale_id'] = $sale ? $sale->id : '';
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

 
    private function sale_balance_add(int $sale_id, $invoice_no, $grand_total, $total_tax,$sum_direct_cost, int $customer_coa_id, string $customer_name, $sale_date, array $payment_data,$warehouse_id) {

        //Inventory Credit
        $coscr = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('inventory'))->value('id'),
            'voucher_no'          => $invoice_no,
            'voucher_type'        => 'INVOICE',
            'voucher_date'        => $sale_date,
            'description'         => 'Inventory Credit For Invoice No '.$invoice_no,
            'debit'               => 0,
            'credit'              => $sum_direct_cost,
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
        ); 

            // customer Debit
            $sale_coa_transaction = array(
            'chart_of_account_id' => $customer_coa_id,
            'voucher_no'          => $invoice_no,
            'voucher_type'        => 'INVOICE',
            'voucher_date'        => $sale_date,
            'description'         => 'Customer debit For Invoice No -  ' . $invoice_no . ' Customer ' .$customer_name,
            'debit'               => $grand_total,
            'credit'              => 0,
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
        );

        
        // $income = $grand_total - $sum_direct_cost - $total_tax;
        $product_sale_income = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('product_sale'))->value('id'),
            'voucher_no'          => $invoice_no,
            'voucher_type'        => 'INVOICE',
            'voucher_date'        => $sale_date,
            'description'         => 'Sale Income For Invoice NO - ' . $invoice_no . ' Customer ' .$customer_name,
            'debit'               => 0,
            'credit'              => $grand_total,
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
        ); 

        Transaction::insert([
            $coscr, $sale_coa_transaction, $product_sale_income
        ]);

        if($total_tax){
            $tax_info = array(
                'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('tax'))->value('id'),
                'voucher_no'          => $invoice_no,
                'voucher_type'        => 'INVOICE',
                'voucher_date'        => $sale_date,
                'description'         => 'Sale Total Tax For Invoice NO - ' . $invoice_no . ' Customer ' .$customer_name,
                'debit'               => $total_tax,
                'credit'              => 0,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            ); 
            Transaction::create($tax_info);
        }
        

        if(!empty($payment_data['paid_amount']))
        {
        
            /****************/
            $customer_credit = array(
                'chart_of_account_id' => $customer_coa_id,
                'voucher_no'          => $invoice_no,
                'voucher_type'        => 'INVOICE',
                'voucher_date'        => $sale_date,
                'description'         => 'Customer credit for Paid Amount For Customer Invoice NO- ' . $invoice_no . ' Customer- ' . $customer_name,
                'debit'               => 0,
                'credit'              => $payment_data['paid_amount'],
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            );
            if($payment_data['payment_method'] == 1){
                //Cah In Hand debit
                $payment = array(
                    'chart_of_account_id' => $payment_data['account_id'],
                    'voucher_no'          => $invoice_no,
                    'voucher_type'        => 'INVOICE',
                    'voucher_date'        => $sale_date,
                    'description'         => 'Cash in Hand in Sale for Invoice No - ' . $invoice_no . ' customer- ' .$customer_name,
                    'debit'               => $payment_data['paid_amount'],
                    'credit'              => 0,
                    'posted'              => 1,
                    'approve'             => 1,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                    
                );
            }else{
                // Bank Ledger
                $payment = array(
                    'chart_of_account_id' => $payment_data['account_id'],
                    'voucher_no'          => $invoice_no,
                    'voucher_type'        => 'INVOICE',
                    'voucher_date'        => $sale_date,
                    'description'         => 'Paid amount for customer  Invoice No - ' . $invoice_no . ' customer -' . $customer_name,
                    'debit'               => $payment_data['paid_amount'],
                    'credit'              => 0,
                    'posted'              => 1,
                    'approve'             => 1,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                );
            }
            Transaction::insert([$customer_credit,$payment]);
            
        }
    }

    

    public function show(int $id)
    {
        if(permission('pos-sale-view')){
            $this->setPageData('Sale Details','Sale Details','fas fa-file',[['name'=>'Sale','link' => url('pos-sale')],['name' => 'Sale Details']]);
            $sale = $this->model->with('sale_products','creator:id,name')->find($id);
            return view('sale::pos-sale.details',compact('sale'));
        }else{
            return $this->access_blocked();
        }
    }
    public function edit(int $id)
    {
        if(permission('pos-sale-edit')){
            $this->setPageData('Edit Sale','Edit Sale','fas fa-edit',[['name'=>'Sale','link' => url('pos-sale')],['name' => 'Edit Sale']]);
            $sale_data = $this->model->with('sale_products')->find($id);
            $data = [
                'sale'      => $sale_data,
                'warehouses' => Warehouse::where('status',1)->get(),
                'taxes'      => Tax::activeTaxes(),
            ];
            return view('sale::pos-sale.edit',$data);
        }else{
            return $this->access_blocked();
        }

    }

    public function update(POSSaleRequest $request)
    {
        if($request->ajax()){
            if(permission('pos-sale-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $sale_data = [
                        'warehouse_id'     => $request->warehouse_id,
                        'customer_name'      => $request->customer_name,
                        'item'             => $request->item,
                        'total_qty'        => $request->total_qty,
                        'total_discount'   => $request->total_discount,
                        'total_tax'        => $request->total_tax,
                        'total_price'      => $request->total_price,
                        'order_tax_rate'   => $request->order_tax_rate,
                        'order_tax'        => $request->order_tax,
                        'order_discount'   => $request->order_discount ? $request->order_discount : 0,
                        'shipping_cost'    => $request->shipping_cost ? $request->shipping_cost : 0,
                        'total_labor_cost' => 0,
                        'grand_total'      => $request->grand_total,
                        'paid_amount'      => $request->grand_total,
                        'due_amount'       => 0,
                        'payment_status'   => 1,
                        'payment_method'   => 1,
                        'account_id'       => 23,
                        'sale_date'        => $request->sale_date,
                        'updated_by'       => Auth::user()->id,
                        
                    ];

                    $payment_data = [
                        'payment_method' => 1,
                        'account_id'     => 23,
                        'paid_amount'    => $request->grand_total,
                    ];

                    $saleData = $this->model->with('sale_products')->find($request->sale_id);

                    if(!$saleData->sale_products->isEmpty())
                    {
                        foreach ($saleData->sale_products as  $sale_product) {
                            $old_sold_qty = $sale_product->pivot->qty;
                            $sale_unit = Unit::find($sale_product->pivot->sale_unit_id);
                            if($sale_unit->operator == '*'){
                                $old_sold_qty = $old_sold_qty * $sale_unit->operation_value;
                            }else{
                                $old_sold_qty = $old_sold_qty / $sale_unit->operation_value;
                            }

                            $warehouse_product = WarehouseProduct::where([
                                'warehouse_id'=>$saleData->warehouse_id,
                                'product_id'=>$sale_product->id,
                                ])->first();
                            $warehouse_product->qty += $old_sold_qty;
                            $warehouse_product->update();

                            $product = Product::find($sale_product->id);
                            $product->qty += $old_sold_qty;
                            $product->update();
                            

                        }
                    }

                    //purchase finish goods
                    $products = [];
                    $direct_cost  = [];
                    if($request->has('products'))
                    {
                        foreach ($request->products as $key => $value) {
                            $unit = Unit::where('unit_name',$value['unit'])->first();
                            if($unit->operator == '*'){
                                $qty = $value['qty'] * $unit->operation_value;
                            }else{
                                $qty = $value['qty'] / $unit->operation_value;
                            }

                            $products[$value['id']] = [
                                'qty'              => $value['qty'],
                                'sale_unit_id'     => $unit ? $unit->id : null,
                                'net_unit_price'   => $value['net_unit_price'],
                                'discount'         => $value['discount'],
                                'tax_rate'         => $value['tax_rate'],
                                'tax'              => $value['tax'],
                                'total'            => $value['subtotal']
                            ];
                            $warehouse_product = WarehouseProduct::where([['warehouse_id',$request->warehouse_id],['product_id',$value['id']],['qty','>',0]])->first();
                            if($warehouse_product){
                                $warehouse_product->qty -= $qty;
                                $warehouse_product->save();
                            }
                            $product_data = Product::where([['id',$value['id']],['qty','>',0]])->first();
                            if($product_data){
                                $direct_cost[] = $qty * ($product_data ? $product_data->cost : 0);
                                $product_data->qty -= $qty;
                                $product_data->update();
                                    
                            }
                            
                            
                        }
                    }
                    $sum_direct_cost = array_sum($direct_cost);
                    $total_tax = ($request->total_tax ? $request->total_tax : 0) + ($request->order_tax ? $request->order_tax : 0);

                    $sale = $saleData->update($sale_data);

                    $saleData->sale_products()->sync($products);
                    // $saleData->labor_bill_rates()->sync($labor_bills);
                    Transaction::where(['voucher_no'=>$request->invoice_no,'voucher_type'=>'INVOICE'])->delete();
                    $customer = Customer::with('coa')->find(1);
                    $this->sale_balance_add($request->sale_id,$request->invoice_no,$request->grand_total,$total_tax,$sum_direct_cost,$customer->coa->id,$customer->name,$request->sale_date,$payment_data,$request->warehouse_id);
                    $output  = $this->store_message($sale, $request->sale_id);
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

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('pos-sale-delete'))
            {
                DB::beginTransaction();
                try {
    
                    $saleData = $this->model->with('sale_products')->find($request->id);
                    $invoice_no = $saleData->invoice_no;
    
                    if(!$saleData->sale_products->isEmpty())
                    {
                        foreach ($saleData->sale_products as  $sale_product) {
                            $old_sold_qty = $sale_product->pivot->qty;
                            $sale_unit = Unit::find($sale_product->pivot->sale_unit_id);
                            if($sale_unit->operator == '*'){
                                $old_sold_qty = $old_sold_qty * $sale_unit->operation_value;
                            }else{
                                $old_sold_qty = $old_sold_qty / $sale_unit->operation_value;
                            }

                            $warehouse_product = WarehouseProduct::where([
                                'warehouse_id'=>$saleData->warehouse_id,
                                'product_id'=>$sale_product->id
                                ])->first();
                            $warehouse_product->qty += $old_sold_qty;
                            $warehouse_product->update();
    
                            $product = Product::find($sale_product->id);
                            $product->qty += $old_sold_qty;
                            $product->update();
                        }
                        $saleData->sale_products()->detach();
                    }

                    Transaction::where(['voucher_no'=>$invoice_no,'voucher_type'=>'INVOICE'])->delete();
    
                    $result = $saleData->delete();
                    if($result)
                    {
                        $output = ['status' => 'success','message' => 'Data has been deleted successfully'];
                    }else{
                        $output = ['status' => 'error','message' => 'failed to delete data'];
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
            if(permission('pos-sale-bulk-delete')){
                
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $saleData = $this->model->with('sale_products')->find($id);
                        $invoice_no = $saleData->invoice_no;
                        $old_document = $saleData ? $saleData->document : '';
        
                        if(!$saleData->sale_products->isEmpty())
                        {
                            foreach ($saleData->sale_products as  $sale_product) {
                                $old_sold_qty = $sale_product->pivot->qty;
                                $sale_unit = Unit::find($sale_product->pivot->sale_unit_id);
                                if($sale_unit->operator == '*'){
                                    $old_sold_qty = $old_sold_qty * $sale_unit->operation_value;
                                }else{
                                    $old_sold_qty = $old_sold_qty / $sale_unit->operation_value;
                                }
                                $warehouse_product = WarehouseProduct::where([
                                    'warehouse_id'=>$saleData->warehouse_id,
                                    'product_id'=>$sale_product->id
                                    ])->first();
                                $warehouse_product->qty += $old_sold_qty;
                                $warehouse_product->update();
        
                                $product = Product::find($sale_product->id);
                                $product->qty += $old_sold_qty;
                                $product->update();
                                
                            }
                            $saleData->sale_products()->detach();
                        }

                        Transaction::where(['voucher_no'=>$invoice_no,'voucher_type'=>'INVOICE'])->delete();
        
                        $result = $saleData->delete();
                    }
                    if($result)
                    {
                        $output = ['status' => 'success','message' => 'Data has been deleted successfully'];
                    }else{
                        $output = ['status' => 'error','message' => 'failed to delete data'];
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
