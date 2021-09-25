<?php

namespace Modules\Production\Entities;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Modules\Material\Entities\Material;
use Modules\Production\Entities\Production;

class ProductionPackagingMaterial extends Model
{
    protected $fillable = ['production_id', 'material_id', 'prefix','unit_id', 'qty', 'rate', 'total'];

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
