<?php

namespace Modules\Formulation\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormulationMaterial extends Model
{
    protected $fillable = ['formulation_id', 'material_id', 'unit_id', 'qty', 'rate', 'total'];

}
