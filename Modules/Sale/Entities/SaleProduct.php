<?php

namespace Modules\Sale\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Product;

class SaleProduct extends Model
{
    protected $fillable = ['sale_id', 'product_id', 'qty', 'delivered','delivered_warehouse_id','sale_unit_id', 'net_unit_price', 'discount', 'tax_rate', 'tax', 'total'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
