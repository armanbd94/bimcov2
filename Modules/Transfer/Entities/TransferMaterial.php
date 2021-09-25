<?php

namespace Modules\Transfer\Entities;

use Illuminate\Database\Eloquent\Model;

class TransferMaterial extends Model
{
    protected $fillable = ['transfer_id', 'material_id', 'qty', 'transfer_unit_id', 'net_unit_cost', 'total'];

}
