<div class="row" id="production_1">
    <div class="col-md-12">
        <div class="card card-custom card-fit card-border">
            <div class="card-body py-5">
                <div class="row">
                    
                        <div class="form-group col-md-4 required">
                            <label >Finish Goods</label>
                            <select name="production[1][product_id]" id="production_1_product_id"  onchange="formulationData(this.value,1)" class="form-control selectpicker">
                                <option value="">Select Please</option>
                                @if (!$products->isEmpty())
                                    @foreach ($products as $product)
                                    <option value="{{ $product->id }}" data-unitid="{{ $product->unit_id }}" data-unitname="{{ $product->unit->unit_name }}">{{ $product->name.' - '.$product->code }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group col-md-4 required">
                            <label >FG Serial No</label>
                            <select name="production[1][formulation_no]" id="production_1_formulation_no" class="form-control selectpicker">
                                <option value="">Select Please</option>
                                
                            </select>
                        </div>
                       
                        <div class="col-md-4">
                            <div class="row" style="justify-content: space-evenly;padding-top: 28px;">
                                <button type="button" class="btn btn-sm btn-success col-md-5" id="active-btn" onclick="showFormulationData('active',1)">Show Active FG SL</button>
                                <button type="button" class="btn btn-sm btn-secondary col-md-5" id="all-btn" onclick="showFormulationData('all',1)">Show All FG SL</button>
                            </div>
                        </div>
                        <div class="form-group col-md-4 required">
                            <label for="serial_no">Serial No</label>
                            <input type="text" class="form-control" name="production[1][serial_no]" id="production_1_serial_no" readonly />
                        </div>
                        <div class="form-group col-md-4 required">
                            <label for="date">Date</label>
                            <input type="text" class="form-control date" name="production[1][date]" id="production_1_date" value="{{ date('Y-m-d') }}" readonly />
                        </div>
                       
                        <div class="form-group col-md-4 required">
                            <label for="unit_name">Unit</label>
                            <input type="text" class="form-control" name="production[1][unit_name]" id="production_1_unit_name" readonly />
                            <input type="hidden" class="form-control" name="production[1][unit_id]" id="production_1_unit_id" readonly />
                        </div>
                        <div class="form-group col-md-4 required">
                            <label for="total_fg_qty">Total FG Quantity</label>
                            <input type="text" class="form-control" name="production[1][total_fg_qty]" id="production_1_total_fg_qty" onkeyup="totalCostCalculation(1)" />
                        </div>
                       
                        <div class="form-group col-md-4 required">
                            <label >Material Warehouse</label>
                            <select name="production[1][mwarehouse_id]" id="production_1_mwarehouse_id" class="form-control selectpicker">
                                <option value="">Select Please</option>
                                @if (!$warehouses->isEmpty())
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach 
                                @endif
                            </select>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 py-5">
        <table class="table table-borderless pb-5" id="material_table_1">
            <thead class="bg-primary">
                <th width="30%">Material</th>
                <th class="text-center">Category</th>
                <th class="text-center">Unit Name</th>
                <th class="text-right">RM Rate</th>
                <th class="text-right">RM Quantity</th>
                <th class="text-right">RM Value</th>
            </thead>
            <tbody></tbody>
        </table>
    </div>      
    <div class="col-md-12 py-5">
        <div class="col-md-12 text-center">
            <h5 class="bg-warning text-white p-3" style="width:250px;margin: 20px auto 10px auto;">Materials Adjustment</h5>
        </div>
        <table class="table table-borderless pb-5" id="material_mixing_table_1">
            <thead class="bg-primary">
                <th width="25%">Material</th>
                <th class="text-center">Category</th>
                <th class="text-center">Prefix</th>
                <th class="text-center">Unit Name</th>
                <th class="text-right">RM Rate</th>
                <th class="text-center">RM Quantity</th>
                <th class="text-right">RM Value</th>
                <th class="text-center"> <i class="fas fa-trash text-white"></i> </th>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select class="form-control selectpicker" name="production[1][packaging_materials][1][material_id]" id="production_1_packaging_materials_1_material_id" data-id="1" onchange="setRowValue(1,1)" data-live-search="true">
                            <option value="">Select Please</option>
                            @if (!$materials->isEmpty())
                                @foreach ($materials as $material)
                                <option value="{{ $material->id }}" data-category="{{ $material->category->name }}" data-unitid="{{ $material->unit_id }}" data-unitname="{{ $material->unit->unit_name }}" data-rate="{{ $material->cost }}">{{ $material->material_name.' - '.$material->material_code }}</option>
                                @endforeach
                            @endif
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control text-center" name="production[1][packaging_materials][1][category]" id="production_1_packaging_materials_1_category" data-id="1" readonly>
                    </td>
                    <td>
                        <select class="form-control prefix" name="production[1][packaging_materials][1][prefix]" id="production_1_packaging_materials_1_prefix" onchange="calculateRowTotal(1,1)" data-id="1">
                            <option value="+">+</option>
                            <option value="-">-</option>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" class="form-control" name="production[1][packaging_materials][1][unit_id]" id="production_1_packaging_materials_1_unit_id" data-id="1">
                        <input type="text" class="form-control text-center" name="production[1][packaging_materials][1][unit_name]" id="production_1_packaging_materials_1_unit_name" data-id="1" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control text-right" name="production[1][packaging_materials][1][rate]" id="production_1_packaging_materials_1_rate" data-id="1" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control text-right qty" name="production[1][packaging_materials][1][qty]" id="production_1_packaging_materials_1_qty" onkeyup="calculateRowTotal(1,1)" data-id="1">
                    </td>
                    <td>
                        <input type="text" class="form-control text-right total" name="production[1][packaging_materials][1][total]" id="production_1_packaging_materials_1_total" data-id="1" readonly>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove_material" data-tab="1"> <i class="fas fa-minus-square text-white"></i> </button>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7"><button type="button" class="btn btn-primary btn-sm add_more_material" id="add_more_material" data-tab="1"> <i class="fas fa-plus-square text-white"></i> </button></td>
                </tr>
            </tfoot>
        </table>

        <table class="table">
            <tr>
                <td class="text-right font-weight-bolder w-50">Total Cost</td>
                <td class="w-50"><input type="text" class="form-control text-right font-weight-bolder" name="production[1][total_cost]" id="production_1_total_cost" readonly /></td>
            </tr>
            <tr>
                <td class="text-right font-weight-bolder w-50">Extra Cost</td>
                <td class="w-50"><input type="text" class="form-control text-right font-weight-bolder" onkeyup="totalCostCalculation(1)" name="production[1][extra_cost]" id="production_1_extra_cost" /></td>
            </tr>
            <tr>
                <td class="text-right font-weight-bolder w-50">Total Net Cost</td>
                <td class="w-50"><input type="text" class="form-control text-right font-weight-bolder" name="production[1][total_net_cost]" id="production_1_total_net_cost" readonly /></td>
            </tr>
            <tr>
                <td class="text-right font-weight-bolder w-50">Per Unit Cost</td>
                <td class="w-50"><input type="text" class="form-control text-right font-weight-bolder" name="production[1][per_unit_cost]" id="production_1_per_unit_cost" readonly /></td>
            </tr>
        </table>
    </div>   
    <div class="col-md-12 text-right pt-5">
        <button type="button" class="btn btn-primary btn-sm" onclick="check_material_stock(1)" id="check-btn-1"> Check</button>
    </div>   
</div>