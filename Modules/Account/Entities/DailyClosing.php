<?php

namespace Modules\Account\Entities;

use App\Models\BaseModel;

class DailyClosing extends BaseModel
{
    protected $fillable = [ 'last_day_closing', 'cash_in', 'cash_out', 'amount', 'adjustment', 'date','thousands', 'five_hundred', 'hundred', 'fifty', 'twenty', 'ten', 'five', 'two', 'one', 'created_by', 'modified_by'];
}
