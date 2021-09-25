<?php

namespace Modules\Production\Entities;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Modules\Material\Entities\Material;

class ProductionMaterial extends Model
{
    protected $fillable = ['pre_production_id', 'material_id', 'unit_id', 'qty', 'rate', 'total'];

    public function production()
    {
        return $this->belongsTo(Production::class); 
    }
    public function material()
    {
        return $this->belongsTo(Material::class); 
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class); 
    }
}
