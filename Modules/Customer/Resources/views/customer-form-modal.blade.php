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
                    <input type="hidden" name="old_name" id="old_name"/>
                    <input type="hidden" name="old_previous_balance" id="old_previous_balance"/>
                    <x-form.textbox labelName="Customer Name" name="name" required="required" col="col-md-6" placeholder="Enter customer name"/>
                    <x-form.textbox labelName="Company Name" name="company_name" col="col-md-6" placeholder="Enter company name"/>
                    <x-form.textbox labelName="Mobile" name="mobile" col="col-md-6" required="required" placeholder="Enter mobile number"/>
                    <x-form.textbox labelName="Email" name="email" type="email" col="col-md-6" placeholder="Enter email address"/>
                    <x-form.selectbox labelName="Customer Group" name="customer_group_id" col="col-md-6" required="required" class="selectpicker">
                      @if (!$customer_groups->isEmpty())
                          @foreach ($customer_groups as $value)
                          <option value="{{ $value->id }}">{{ $value->group_name }}</option>
                          @endforeach
                      @endif
                    </x-form.selectbox>
                    <x-form.textbox labelName="City" name="city" col="col-md-6" placeholder="Enter city name"/>
                    <x-form.textbox labelName="Zipcode" name="zipcode" col="col-md-6" placeholder="Enter zipcode"/>
                    <x-form.textbox labelName="Previous Balance" name="previous_balance" col="col-md-6 pbalance d-none" placeholder="Previous credit balalnce"/>
                    <x-form.textarea labelName="Customer Address" name="address" col="col-md-6" required="required" placeholder="Enter customer address"/>
                    
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
