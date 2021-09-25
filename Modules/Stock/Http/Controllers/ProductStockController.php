<?php

namespace Modules\Stock\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Product\Entities\WarehouseProduct;

class ProductStockController extends BaseController
{

    public function __construct(WarehouseProduct $model)
    {
        $this->model = $model;
    }
    public function index()
    {
        if(permission('finish-goods-stock-access')){
            $this->setPageData('Finish Goods Stock','Finish Goods Stock','fas fa-boxes',[['name' => 'Finish Goods Stock']]);
            $data = [
                'categories' => Category::with('warehouse_products')->whereHas('warehouse_products')->where([['type',2],['status',1]])->get(),
                'warehouses' => DB::table('warehouses')->select('id','name')->where('status',1)->get()
            ];
            return view('stock::product.index',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_product_stock_data(Request $request)
    {
        if($request->ajax())
        {
            $warehouse_id = $request->warehouse_id;
            $product_id = $request->product_id;
            $category_id = $request->category_id;
            $categories = Category::with('warehouse_products')
            ->when($category_id, function($q) use ($category_id){
                $q->where('id',$category_id);
            })
            ->whereHas('warehouse_products',function($query) use ($product_id,$warehouse_id){
                $query->where('warehouse_id',$warehouse_id)
                ->when($product_id, function($q,$product_id){
                    $q->where('product_id',$product_id);
                });
            });

            $categories = $categories->where([['type',2],['status',1]])->get();
            return view('stock::product.product-list',compact('categories','product_id','category_id','warehouse_id'))->render();
        }
    }

    public function product_search(Request $request)
    {
        if ($request->ajax()) {
            if(!empty($request->search)){
                $data = Product::where('name', 'like','%'.$request->search.'%')->get();
                $output = array();
                if(!empty($data) && count($data) > 0)
                {
                    foreach($data as $row)
                    {
                        $temp_array             = array();
                        $temp_array['id']       = $row->id;
                        $temp_array['value']    = $row->name;
                        $temp_array['label']    = $row->name;
                        $output[]               = $temp_array;
                    }
                } else{
                    $output['value']            = '';
                    $output['label']            = 'No Record Found';
                }
                return $output; 
            }
        }
    }
}
