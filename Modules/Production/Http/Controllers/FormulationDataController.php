<?php

namespace Modules\Production\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;
use Modules\Formulation\Entities\Formulation;

class FormulationDataController extends BaseController
{
    public function __construct(Formulation $model)
    {
        $this->model = $model;
    }
    
    public function index(Request $request)
    {
        if($request->ajax())
        {
            $output = '';
            $formulations = $this->model->with('unit')->where('product_id',$request->product_id)->orderBy('id','asc')->get();
            if(!$formulations->isEmpty())
            {
                $output .= '<option value="">Select Please</option>';
                foreach ($formulations as $formulation) {
                    $active = $formulation->status == 1 ? 'selected' : 'disabled';
                    $background = ($active == 'disabled') ? 'bg-secondary' : '';
                    $output .= '<option value="'.$formulation->id.'" class="'.$background.'" '.$active.' data-unitid="'.$formulation->unit_id.'" data-unitname="'.$formulation->unit->unit_name.'" data-totalfgqty="'.$formulation->total_fg_qty.'">'.$formulation->formulation_no.'</option>';
                }
            }
            return $output;
        }
    }

    public function formulation_materials(Request $request)
    {
        if($request->ajax())
        {
            $formulation = $this->model->with('materials','unit')->findOrFail($request->formulation_id);
            $materials   =  Material::with('unit:id,unit_name','category:id,name')->where('status',1)->get();
            return view('production::formulation-materials.index',compact('formulation','materials'))->render();
        }
    }

    public function pre_production_formulation_materials(Request $request)
    {
        if($request->ajax())
        {
            $formulation = $this->model->with('materials','unit')->findOrFail($request->formulation_id);
            $materials   =  Material::with('unit:id,unit_name','category:id,name')->where('status',1)->get();
            $tab = $request->tab;
            return view('production::formulation-materials.pre-production-formulation',compact('formulation','materials','tab'))->render();
        }
    }
}
