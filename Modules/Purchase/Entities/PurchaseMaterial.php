<?php

namespace Modules\Purchase\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseMaterial extends Model
{
    protected $fillable = ['purchase_id', 'material_id', 'qty', 'received', 'purchase_unit_id', 'net_unit_cost', 
    'discount', 'tax_rate', 'tax', 'total'];
}
