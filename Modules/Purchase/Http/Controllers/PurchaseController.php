<?php

namespace Modules\Purchase\Http\Controllers;

use Exception;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Material\Entities\Material;
use Modules\Purchase\Entities\Purchase;
use Modules\Setting\Entities\LaborBill;
use Modules\Setting\Entities\Warehouse;
use Modules\Supplier\Entities\Supplier;
use App\Http\Controllers\BaseController;
use App\Notifications\MaterialPurchased;
use Modules\Account\Entities\Transaction;
use Illuminate\Support\Facades\Notification;
use Modules\Purchase\Entities\PurchasePayment;
use Modules\Material\Entities\WarehouseMaterial;
use Modules\Purchase\Http\Requests\PurchaseFormRequest;
use Illuminate\Support\Facades\Session;
use Modules\Purchase\Entities\PurchaseMaterial;

class PurchaseController extends BaseController
{
    
    use UploadAble;
    private const invoice_no = 1001;
    public function __construct(Purchase $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('purchase-access')){
            $this->setPageData('Purchase Manage','Purchase Manage','fas fa-shopping-cart',[['name' => 'Purchase Manage']]);
            $suppliers  = Supplier::allSuppliers();
            $warehouses = Warehouse::activeWarehouses();
            return view('purchase::index',compact('suppliers','warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('purchase-access')){

                if (!empty($request->invoice_no)) {
                    $this->model->setMemoNo($request->invoice_no);
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
                if (!empty($request->supplier_id)) {
                    $this->model->setSupplierID($request->supplier_id);
                }
                if (!empty($request->purchase_status)) {
                    $this->model->setPurchaseStatus($request->purchase_status);
                }
                if (!empty($request->payment_status)) {
                    $this->model->setPaymentStatus($request->payment_status);
                }


                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('purchase-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("purchase.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('purchase-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("purchase.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }

                    if(permission('purchase-payment-add')){
                        if($value->payment_status != 1){
                        $action .= ' <a class="dropdown-item add_payment" data-id="'.$value->id.'" data-due="'.($value->grand_total - $value->paid_amount).'"><i class="fas fa-plus-square text-info mr-2"></i> Add Payment</a>';
                        }
                    }
                    if(permission('purchase-payment-view')){
                        $action .= ' <a class="dropdown-item view_payment_list"  data-id="'.$value->id.'"><i class="fas fa-file-invoice-dollar text-dark mr-2"></i> Payment List</a>';
                    }
                    
                    if(permission('purchase-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->invoice_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('purchase-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->invoice_no;
                    $row[] = $value->warehouse->name;
                    $row[] = $value->supplier->company_name.' ( '.$value->supplier->name.')';
                    $row[] = $value->item;
                    $row[] = number_format($value->total_cost,4);
                    $row[] = $value->shipping_cost ? number_format($value->shipping_cost,4) : 0;
                    $row[] = number_format($value->grand_total,4);
                    $row[] = number_format($value->paid_amount,4);
                    $row[] = number_format(($value->grand_total - $value->paid_amount),4);
                    $row[] = date(config('settings.date_format'),strtotime($value->purchase_date));
                    $row[] = PURCHASE_STATUS_LABEL[$value->purchase_status];
                    $row[] = PAYMENT_STATUS_LABEL[$value->payment_status];
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
        if(permission('purchase-add')){
            $this->setPageData('Add Purchase','Add Purchase','fas fa-shopping-cart',[['name' => 'Add Purchase']]);
            $purchase = $this->model->select('invoice_no')->orderBy('invoice_no','desc')->first();
            $data = [
                'warehouses' => Warehouse::activeWarehouses(),
                'suppliers'  => Supplier::allSuppliers(),
                'taxes'      => Tax::activeTaxes(),
                'purchase_data' => Session::get('purchase_data'),
                'invoice_no'  => $purchase ? 'PRC-'.(explode('PRC-',$purchase->invoice_no)[1] + 1) : 'PRC-1001'
            ];
            return view('purchase::create',$data);
        }else{
            return $this->access_blocked();
        }
        
    }

    public function store(PurchaseFormRequest $request)
    {
        if($request->ajax()){
            if(permission('purchase-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $purchase_data = [
                        'invoice_no'       => $request->invoice_no,
                        'supplier_id'      => $request->supplier_id,
                        'warehouse_id'     => $request->warehouse_id,
                        'item'             => $request->item,
                        'total_qty'        => $request->total_qty,
                        'total_discount'   => $request->total_discount,
                        'total_tax'        => $request->total_tax,
                        'total_labor_cost' => 0,
                        'total_cost'       => $request->total_cost,
                        'order_tax_rate'   => $request->order_tax_rate,
                        'order_tax'        => $request->order_tax,
                        'order_discount'   => $request->order_discount ? $request->order_discount : null,
                        'shipping_cost'    => $request->shipping_cost ? $request->shipping_cost : null,
                        'grand_total'      => $request->grand_total,
                        'paid_amount'      => $request->paid_amount,
                        'purchase_status'  => $request->purchase_status,
                        'payment_status'   => $request->payment_status,
                        'note'             => $request->note,
                        'purchase_date'    => $request->purchase_date,
                        'created_by'       => auth()->user()->id
                    ];

                   

                    $payment_data = [
                        'payment_method' => $request->payment_method,
                        'account_id'     => $request->account_id,
                        'paid_amount'    => $request->paid_amount,
                        'cheque_no'      => $request->cheque_number ? $request->cheque_number : '',
                    ];

                    //purchase materials
                    $materials = [];
                    if($request->has('materials'))
                    {
                        foreach ($request->materials as $key => $value) {
                            $unit = Unit::where('unit_name',$value['unit'])->first();
                            if($unit->operator == '*'){
                                $qty = $value['received'] * $unit->operation_value;
                            }else{
                                $qty = $value['received'] / $unit->operation_value;
                            }

                            $material_data = Material::find($value['id']);

                            $current_stock_value = ($material_data->qty ? $material_data->qty : 0) * ($material_data->cost ? $material_data->cost : 0);
                            $cost = ($value['subtotal'] + $current_stock_value) / ($qty + $material_data->qty);
                            $old_cost = $material_data->cost ? $material_data->cost : 0;
                            
                            $material_data->qty += $qty;
                            $material_data->cost    = $cost;
                            $material_data->old_cost = $old_cost;
                            
                            $material_data->update();

                            $materials[$value['id']] = [
                                'qty'              => $value['qty'],
                                'received'         => $value['received'],
                                'purchase_unit_id' => $unit ? $unit->id : null,
                                'net_unit_cost'    => $value['net_unit_cost'],
                                'new_unit_cost'    => $cost,
                                'discount'         => $value['discount'],
                                'tax_rate'         => $value['tax_rate'],
                                'tax'              => $value['tax'],
                                'total'            => $value['subtotal']
                            ];
                            
                            $warehouse_material = WarehouseMaterial::where([
                                'warehouse_id' => $request->warehouse_id,
                                'material_id'  => $value['id']])->first();
                            if($warehouse_material){
                                $warehouse_material->qty += $qty;
                                $warehouse_material->update();
                            }else{
                                WarehouseMaterial::create([
                                    'warehouse_id' => $request->warehouse_id,
                                    'material_id'  => $value['id'],
                                    'qty'          => $qty
                                ]);
                            }

                        }
                    }

                    
                    $result  = $this->model->create($purchase_data);

                    $purchase = $this->model->with('purchase_materials')->find($result->id);
                    if(count($materials) > 0){
                        $purchase->purchase_materials()->sync($materials);
                    }

                    $supplier = Supplier::with('coa')->find($request->supplier_id);
                    $this->purchase_balance_add($result->invoice_no,$result->id,$request->grand_total,$supplier->coa->id,$supplier->name,$request->purchase_date,$payment_data);
                    
                    
                    $output  = $this->store_message($result, $request->update_id);
                    if($result){
                        Session::forget('purchase_data');
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

 
    private function purchase_balance_add(string $purchase_no,string $purchase_id,$balance, int $supplier_coa_id, string $supplier_name, $purchase_date, array $payment_data) {
        if(!empty($purchase_no) && !empty($purchase_id) && !empty($balance) && !empty($supplier_coa_id) && !empty($supplier_name)  && !empty($purchase_date)){
            // supplier Credit
            $purchase_coa_transaction = array(
                'chart_of_account_id' => $supplier_coa_id,
                'voucher_no'          => $purchase_no,
                'voucher_type'        => 'Purchase',
                'voucher_date'        => $purchase_date,
                'description'         => 'Supplier '.$supplier_name,
                'debit'               => 0,
                'credit'              => $balance,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            );

            //Inventory Debit
            $cosde = array(
                'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('inventory'))->value('id'),
                'voucher_no'          => $purchase_no,
                'voucher_type'        => 'Purchase',
                'voucher_date'        => $purchase_date,
                'description'         => 'Inventory Debit For Supplier '.$supplier_name,
                'debit'               => $balance,
                'credit'              => 0,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            ); 

             // Expense for company
            $expense = array(
                'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('material_purchase'))->value('id'),
                'voucher_no'          => $purchase_no,
                'voucher_type'        => 'Purchase',
                'voucher_date'        => $purchase_date,
                'description'         => 'Company Credit For Supplier '.$supplier_name,
                'debit'               => $balance,
                'credit'              => 0,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            ); 

            Transaction::insert([
                $purchase_coa_transaction,$cosde,$expense
            ]);


            if($payment_data['paid_amount'])
            {
                /****************/
                $supplierdebit = array(
                    'chart_of_account_id' => $supplier_coa_id,
                    'voucher_no'          => $purchase_no,
                    'voucher_type'        => 'Purchase',
                    'voucher_date'        => $purchase_date,
                    'description'         => 'Supplier .' . $supplier_name,
                    'debit'               => $payment_data['paid_amount'],
                    'credit'              => 0,
                    'posted'              => 1,
                    'approve'             => 1,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                );
                if($payment_data['payment_method'] == 1){
                    //Cah In Hand For Supplier
                    $payment = array(
                        'chart_of_account_id' => $payment_data['account_id'],
                        'voucher_no'          => $purchase_no,
                        'voucher_type'        => 'Purchase',
                        'voucher_date'        => $purchase_date,
                        'description'         => 'Cash in Hand For Supplier ' . $supplier_name,
                        'debit'               => 0,
                        'credit'              => $payment_data['paid_amount'],
                        'posted'              => 1,
                        'approve'             => 1,
                        'created_by'          => auth()->user()->name,
                        'created_at'          => date('Y-m-d H:i:s')
                        
                    );
                }else{
                    // Bank Ledger
                    $payment = array(
                        'chart_of_account_id' => $payment_data['account_id'],
                        'voucher_no'          => $purchase_no,
                        'voucher_type'        => 'Purchase',
                        'voucher_date'        => $purchase_date,
                        'description'         => 'Paid amount for Supplier  ' . $supplier_name,
                        'debit'               => 0,
                        'credit'              => $payment_data['paid_amount'],
                        'posted'              => 1,
                        'approve'             => 1,
                        'created_by'          => auth()->user()->name,
                        'created_at'          => date('Y-m-d H:i:s')
                    );
                }

                $supplier_debit_transaction = Transaction::create($supplierdebit);
                $payment_transaction        = Transaction::create($payment);

                if($supplier_debit_transaction && $payment_transaction){
                    PurchasePayment::create([
                        'purchase_id'                   => $purchase_id,
                        'account_id'                    => $payment_data['account_id'],
                        'transaction_id'                => $payment_transaction->id,
                        'supplier_debit_transaction_id' => $supplier_debit_transaction->id,
                        'amount'                        => $payment_data['paid_amount'],
                        'payment_method'                => $payment_data['payment_method'],
                        'cheque_no'                     => $payment_data['cheque_no'],
                        'created_by'                    => auth()->user()->name
                    ]);
                }
            }
        }
    }


    public function show(int $id)
    {
        if(permission('purchase-view')){
            $this->setPageData('Purchase Details','Purchase Details','fas fa-file',[['name'=>'Purchase','link' => route('purchase')],['name' => 'Purchase Details']]);
            $purchase = $this->model->with('purchase_materials','supplier','creator:id,name')->find($id);
            return view('purchase::details',compact('purchase'));
        }else{
            return $this->access_blocked();
        }
    }
    public function edit(int $id)
    {

        if(permission('purchase-edit')){
            $this->setPageData('Edit Purchase','Edit Purchase','fas fa-edit',[['name'=>'Purchase','link' => route('purchase')],['name' => 'Edit Purchase']]);
            $data = [
                'purchase'   => $this->model->with('purchase_materials')->find($id),
                'warehouses' => Warehouse::activeWarehouses(),
                'suppliers'  => Supplier::allSuppliers(),
                'taxes'      => Tax::activeTaxes(),

            ];
            return view('purchase::edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(PurchaseFormRequest $request)
    {
        if($request->ajax()){
            if(permission('purchase-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $purchaseData = $this->model->with('purchase_materials')->find($request->purchase_id);
                    $balance = $request->grand_total - $purchaseData->paid_amount;
                    if($balance == 0)
                    {
                        $payment_status = 1;//paid
                    }else if($balance == $purchaseData->grand_total)
                    {
                        $payment_status = 3;//due
                    }else{
                        $payment_status = 2;//partial
                    }

                    $purchase_data = [
                        'invoice_no'       => $request->invoice_no,
                        'supplier_id'      => $request->supplier_id,
                        'warehouse_id'     => $request->warehouse_id,
                        'item'             => $request->item,
                        'total_qty'        => $request->total_qty,
                        'total_discount'   => $request->total_discount,
                        'total_tax'        => $request->total_tax,
                        'total_labor_cost' =>  0,
                        'total_cost'       => $request->total_cost,
                        'order_tax_rate'   => $request->order_tax_rate,
                        'order_tax'        => $request->order_tax,
                        'order_discount'   => $request->order_discount ? $request->order_discount : null,
                        'shipping_cost'    => $request->shipping_cost ? $request->shipping_cost : null,
                        'grand_total'      => $request->grand_total,
                        'purchase_status'  => $request->purchase_status,
                        'payment_status'   => $payment_status,
                        'note'             => $request->note,
                        'purchase_date'    => $request->purchase_date,
                        'modified_by'       => auth()->user()->id
                    ];

                    $purchase_no = $purchaseData->invoice_no;
                    $old_supplier_id = $purchaseData->supplier_id;

                    if(!$purchaseData->purchase_materials->isEmpty())
                    {
                        foreach ($purchaseData->purchase_materials as  $purchase_material) {
                            
                            $old_received_qty = $purchase_material->pivot->received;
                            $purchase_unit = Unit::find($purchase_material->pivot->purchase_unit_id);
                            if($purchase_unit->operator == '*'){
                                $old_received_qty = $old_received_qty * $purchase_unit->operation_value;
                            }else{
                                $old_received_qty = $old_received_qty / $purchase_unit->operation_value;
                            }

                            $material_data = Material::find($purchase_material->id);
                            
                            $material_data->qty -= $old_received_qty;
                            $material_data->cost = $material_data->old_cost;
                            $material_data->update();

                            $warehouse_material = WarehouseMaterial::where([
                                ['warehouse_id',$purchaseData->warehouse_id],
                                ['material_id',$purchase_material->id],['qty','>',0]])->first();
                            if($warehouse_material){
                                $warehouse_material->qty -= $old_received_qty;
                                $warehouse_material->update();
                            }
                                
                            
                        }
                    }
                    
                    //purchase materials
                    $materials = [];
                    if($request->has('materials'))
                    {
                        foreach ($request->materials as $key => $value) {
                            $unit = Unit::where('unit_name',$value['unit'])->first();
                            if($unit->operator == '*'){
                                $qty = $value['received'] * $unit->operation_value;
                            }else{
                                $qty = $value['received'] / $unit->operation_value;
                            }

                            $material = Material::find($value['id']);
                           
                            $current_stock_value = ($material_data->qty ? $material_data->qty : 0) * ($material_data->cost ? $material_data->cost : 0);
                            $cost = ($value['subtotal'] + $current_stock_value) / ($qty + $material_data->qty);
                            $old_cost = $material_data->cost ? $material_data->cost : 0;

                            $material->qty += $qty;
                            $material->cost    = $cost;
                            $material->old_cost = $old_cost;
                            $material->update();

                            $materials[$value['id']] = [
                                'qty'              => $value['qty'],
                                'received'         => $value['received'],
                                'purchase_unit_id' => $unit ? $unit->id : null,
                                'net_unit_cost'    => $value['net_unit_cost'],
                                'new_unit_cost'    => $cost,
                                'discount'         => $value['discount'],
                                'tax_rate'         => $value['tax_rate'],
                                'tax'              => $value['tax'],
                                'total'            => $value['subtotal']
                            ];

                            $warehouse_material = WarehouseMaterial::where(['warehouse_id'=>$request->warehouse_id,'material_id'=>$value['id']])->first();
                            if($warehouse_material){
                                $warehouse_material->qty += $qty;
                                $warehouse_material->update();
                            }else{
                                WarehouseMaterial::create([
                                    'warehouse_id'=>$request->warehouse_id,
                                    'material_id'=>$value['id'],
                                    'qty' => $qty
                                ]);
                            }
                            
                        }
                    }

                    $purchase = $purchaseData->update($purchase_data);
                    $purchaseData->purchase_materials()->sync($materials);
                    
                    $purchaseData = $this->model->with('purchase_materials')->find($request->purchase_id);
                    
                    if($purchaseData->status == 1){
                        if(!$purchaseData->purchase_materials->isEmpty())
                        {
                            foreach ($purchaseData->purchase_materials as  $purchase_material) {

                                $purchaseMaterial = DB::table('purchase_materials as pm')
                                ->selectRaw('sum(pm.total) as total_material_cost,sum(pm.qty) as total_material_qty,sum(p.shipping_cost) as total_shipping_cost')
                                ->join('purchases as p','pm.purchase_id','=','p.id')
                                ->where([['pm.material_id',$purchase_material->id],['p.status',1]])
                                ->first();
                                $material_cost = ($purchaseMaterial->total_material_cost + $purchaseMaterial->total_shipping_cost) / $purchaseMaterial->total_material_qty;
                                $material_data = Material::find($purchase_material->id);
                                $material_data->cost = number_format($material_cost,4,'.','');
                                $material_data->update();
                            }
                        }
                    }

                    $supplier = Supplier::with('coa')->find($request->supplier_id);
                    if($request->supplier_id == $old_supplier_id){
                        $old_supplier_coa_id = $supplier->coa->id;
                    }else{
                        $old_supplier = Supplier::with('coa')->find($old_supplier_id);
                        $old_supplier_coa_id = $old_supplier->coa->id;
                    }
                    
                    $this->purchase_balance_update($purchase_no,$request->purchase_id,$request->grand_total,$supplier->coa->id,$supplier->name,$request->purchase_date,$old_supplier_coa_id);
                    $output  = $this->store_message($purchase, $request->purchase_id);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    private function purchase_balance_update(string $purchase_no, string $purchase_id,$balance, int $supplier_coa_id, string $supplier_name,$purchase_date, $old_supplier_coa_id) {
        if(!empty($purchase_id) && !empty($balance) && !empty($supplier_coa_id) && !empty($supplier_name) && !empty($purchase_date) && !empty($old_supplier_coa_id)){

            if($supplier_coa_id != $old_supplier_coa_id)
            {
                PurchasePayment::where('purchase_id', $purchase_id)->delete();
                $remove_purchase_transaction = Transaction::where('voucher_no', (string) $purchase_no)->where('voucher_type', (string) "Purchase")->delete();
                // dd($remove_purchase_transaction);
                if($remove_purchase_transaction)
                {
                    $this->purchase_balance_add($purchase_no,$purchase_id,$balance,$supplier_coa_id,$supplier_name,$purchase_date,$payment_data = []);
                }
            }else{
                $purchase_coa_transaction = Transaction::where(['chart_of_account_id' => $supplier_coa_id,'voucher_no' => $purchase_id,'voucher_type'=> 'Purchase'])->first();
                if($purchase_coa_transaction)
                {
                    $purchase_coa_transaction->update([
                        'voucher_date' => $purchase_date,
                        'credit'       => $balance,
                        'modified_by'  => auth()->user()->name,
                        'updated_at'   => date('Y-m-d H:i:s')
                    ]);
                }

                $purchase_coscr = Transaction::where([
                    'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('inventory'))->value('id'),
                    'voucher_no' => $purchase_id,'voucher_type'=> 'Purchase'])->first();
                if($purchase_coscr)
                {
                    $purchase_coscr->update([
                        'voucher_date' => $purchase_date,
                        'debit'        => $balance,
                        'modified_by'  => auth()->user()->name,
                        'updated_at'   => date('Y-m-d H:i:s')
                    ]);
                }
                $company_expense = Transaction::where([
                    'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('material_purchase'))->value('id'),
                    'voucher_no' => $purchase_id,'voucher_type'=> 'Purchase'])->first();
                if($company_expense)
                {
                    $company_expense->update([
                        'voucher_date' => $purchase_date,
                        'debit'        => $balance,
                        'modified_by'  => auth()->user()->name,
                        'updated_at'   => date('Y-m-d H:i:s')
                    ]);
                }

            }

        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('purchase-delete')){
                DB::beginTransaction();
                try {
                    $purchaseData = Purchase::with('purchase_materials')->find($request->id);
                    $invoice_no = $purchaseData->invoice_no;
                    if(!$purchaseData->purchase_materials->isEmpty())
                    {
                        foreach ($purchaseData->purchase_materials as  $purchase_material) {
                            $purchase_unit = Unit::find($purchase_material->pivot->purchase_unit_id);
                            if($purchase_unit->operator == '*'){
                                $received_qty = $purchase_material->pivot->received * $purchase_unit->operation_value;
                            }else{
                                $received_qty = $purchase_material->pivot->received / $purchase_unit->operation_value;
                            }
                            

                            $material_data = Material::find($purchase_material->id);
                            if($material_data->qty > 0){
                                $material_data->qty -= $received_qty;
                                $material_data->cost = $material_data->old_cost;
                                $material_data->update();
                            }

                            $warehouse_material = WarehouseMaterial::where([
                                'warehouse_id'=>$purchaseData->warehouse_id,
                                'material_id'=>$purchase_material->id])->first();
                            if($warehouse_material){
                                if($warehouse_material->qty > 0)
                                {
                                    $warehouse_material->qty -= $received_qty;
                                    $warehouse_material->update();
                                }
                            }
                        }
                        $purchaseData->purchase_materials()->detach();
                    }
                    
                    PurchasePayment::where('purchase_id',$request->id)->delete();
                    Transaction::where('voucher_no', (string) $invoice_no)->where('voucher_type', (string) "Purchase")->delete();

                    $result = $purchaseData->delete();
                    $output = $result ? ['status' => 'success','message' => 'Data has been deleted successfully'] : ['status' => 'error','message' => 'failed to delete data'];
                    DB::commit();
                } catch (Exception $e) {
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

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('purchase-bulk-delete')){
                
                    DB::beginTransaction();
                    try {
                        foreach ($request->ids as $id) {
                            $purchaseData = Purchase::with('purchase_materials')->find($id);
                            $invoice_no = $purchaseData->invoice_no;
                            if(!$purchaseData->purchase_materials->isEmpty())
                            {
                                foreach ($purchaseData->purchase_materials as  $purchase_material) {
                                    $purchase_unit = Unit::find($purchase_material->pivot->purchase_unit_id);
                                    if($purchase_unit->operator == '*'){
                                        $received_qty = $purchase_material->pivot->received * $purchase_unit->operation_value;
                                    }else{
                                        $received_qty = $purchase_material->pivot->received / $purchase_unit->operation_value;
                                    }
                                    
        
                                   $material_data = Material::find($purchase_material->id);
                                    if($material_data->qty > 0){
                                        $material_data->qty -= $received_qty;
                                        $material_data->cost = $material_data->old_cost;
                                        $material_data->update();
                                    }
        
                                    $warehouse_material = WarehouseMaterial::where([
                                        'warehouse_id'=>$purchaseData->warehouse_id,
                                        'material_id'=>$purchase_material->id])->first();
                                    if($warehouse_material){
                                        if($warehouse_material->qty > 0)
                                        {
                                            $warehouse_material->qty -= $received_qty;
                                            $warehouse_material->update();
                                        }
                                    }
                                }
                                $purchaseData->purchase_materials()->detach();
                            }
                            
                            PurchasePayment::where('purchase_id',$id)->delete();
                            Transaction::where('voucher_no', (string) $invoice_no)->where('voucher_type', (string) "Purchase")->delete();
        
                            $result = $purchaseData->delete();
                    
                        }
                        $output = $result ? ['status' => 'success','message' => 'Data has been deleted successfully'] : ['status' => 'error','message' => 'failed to delete data'];
                        DB::commit();
                    } catch (Exception $e) {
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


    public function hold(Request $request)
    {
        if($request->ajax()){
            if(permission('purchase-add')){
                // dd($request->all());
                try {
                    $purchase_data = [
                        'invoice_no'       => $request->invoice_no,
                        'supplier_id'      => $request->supplier_id,
                        'warehouse_id'     => $request->warehouse_id,
                        'item'             => $request->item,
                        'total_qty'        => $request->total_qty,
                        'total_discount'   => $request->total_discount,
                        'total_tax'        => $request->total_tax,
                        'total_labor_cost' => null,
                        'total_cost'       => $request->total_cost,
                        'order_tax_rate'   => $request->order_tax_rate,
                        'order_tax'        => $request->order_tax,
                        'order_discount'   => $request->order_discount ? $request->order_discount : null,
                        'shipping_cost'    => $request->shipping_cost ? $request->shipping_cost : null,
                        'grand_total'      => $request->grand_total,
                        'paid_amount'      => $request->paid_amount ? $request->paid_amount : 0,
                        'due_amount'      =>  ($request->grand_total - ($request->paid_amount ? $request->paid_amount : 0)),
                        'purchase_status'  => $request->purchase_status,
                        'payment_status'   => $request->payment_status,
                        'note'             => $request->note,
                        'purchase_date'    => $request->purchase_date,
                        'created_by'       => auth()->user()->name,
                        'payment_method'   => $request->payment_method ? $request->payment_method : null,
                        'account_id'       => $request->account_id ? $request->account_id : null,
                        'cheque_no'      => $request->cheque_number ? $request->cheque_number : '',
                    ];
                    //purchase materials
                    $materials = [];
                    if($request->has('materials'))
                    {
                        foreach ($request->materials as $key => $value) {
                            $unit = Unit::where('unit_name',$value['unit'])->first();

                            $materials[] = [
                                'id'               => $value['id'],
                                'name'             => $value['name'],
                                'code'             => $value['code'],
                                'qty'              => $value['qty'],
                                'received'         => $value['received'],
                                'unit_name'        => $value['unit'],
                                'purchase_unit_id' => $unit ? $unit->id : null,
                                'net_unit_cost'    => $value['net_unit_cost'],
                                'discount'         => $value['discount'],
                                'tax_rate'         => $value['tax_rate'],
                                'tax'              => $value['tax'],
                                'total'            => $value['subtotal']
                            ];
                        }
                    }

                    $data = [
                        'purchase' => $purchase_data,
                        'materials'=>$materials,
                    ];
                    Session::put('purchase_data',$data);
                   if(Session::get('purchase_data')){
                    $output = ['status' => 'success','message' => 'Purchase Data Holded'];
                   }else{
                    $output = ['status' => 'error','message' => 'Failed to Hold Purchase Data'];
                   }
                    
                } catch (Exception $e) {
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
