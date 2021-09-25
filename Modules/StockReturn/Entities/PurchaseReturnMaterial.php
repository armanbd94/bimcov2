<?php

namespace Modules\StockReturn\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Material\Entities\Material;

class PurchaseReturnMaterial extends Model
{
    protected $fillable = ['purchase_return_id', 'invoice_no', 'material_id', 'return_qty', 'unit_id',
     'material_rate', 'before_purchase_unit_cost', 'after_purchase_unit_cost', 'after_return_unit_cost','deduction_rate', 'deduction_amount', 'total'];

     public function material()
    {
        return $this->belongsTo(Material::class);
    }
    
}
