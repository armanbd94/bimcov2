<div class="modal fade" id="store_or_update_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">

      <!-- Modal Content -->
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header bg-primary">
          <h3 class="modal-title text-white" id="model-1"></h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close text-white"></i>
          </button>
        </div>
        <!-- /modal header -->
        <form id="store_or_update_form" method="post">
          @csrf
            <!-- Modal Body -->
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="update_id" id="update_id"/>
                    <input type="hidden" name="unit_id" id="unit_id" />
                    <x-form.textbox labelName="Date" name="damage_date" col="col-md-6" value="{{ date('Y-m-d') }}" required="required" class="date" property="readonly" />
                    <x-form.selectbox labelName="Warehouse" name="warehouse_id" required="required" onchange="materialList(this.value,2)" col="col-md-6" class="selectpicker">
                        @if (!$warehouses->isEmpty())
                            @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        @endif
                    </x-form.selectbox>
                    <x-form.selectbox labelName="Material" name="material_id" required="required" col="col-md-6" class="selectpicker"/>
                    <x-form.textbox labelName="Net Unit Cost" name="net_unit_cost" col="col-md-6" required="required" property="readonly" />
                    <x-form.textbox labelName="Unit Name" name="unit_name" required="required" col="col-md-6" property="readonly" />
                    <x-form.textbox labelName="Available Stock Quantity" name="stock_qty" col="col-md-6" required="required" property="readonly" />
                    <x-form.textbox labelName="Damage Quantity" name="qty" col="col-md-6" required="required"  />
                    <x-form.textbox labelName="Total Damage Cost" name="total" col="col-md-6" required="required" property="readonly" />
                </div>
            </div>
            <!-- /modal body -->

            <!-- Modal Footer -->
            <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary btn-sm" id="save-btn"></button>
            </div>
            <!-- /modal footer -->
        </form>
      </div>
      <!-- /modal content -->

    </div>
  </div>
