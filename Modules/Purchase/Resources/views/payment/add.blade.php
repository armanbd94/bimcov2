<div class="modal fade" id="payment_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog" role="document">

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
        <form id="payment_form" method="post">
          @csrf
            <!-- Modal Body -->
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="payment_id" id="payment_id"/>
                    <input type="hidden" name="purchase_id" id="purchase_id"/>
                    <div class="form-group col-md-12">
                      <label for="due_amount">Due Amount</label>
                      <input type="text" class="form-control" name="due_amount" id="due_amount" readonly>
                    </div>
                    <x-form.textbox labelName="Payable Amount" name="amount" required="required" col="col-md-12"/>
                    <x-form.textbox labelName="Date" name="date" value="{{ date('Y-m-d') }}" property="readonly" required="required" class="date" col="col-md-12"/>
                    <x-form.selectbox labelName="Payment Method" name="payment_method" required="required"  col="col-md-12" class="selectpicker">
                      @foreach (PAYMENT_METHOD as $key => $value)
                      <option value="{{ $key }}">{{ $value }}</option>
                      @endforeach
                    </x-form.selectbox>
                    <x-form.selectbox labelName="Account" name="account_id" required="required"  col="col-md-12" class="selectpicker"/>
                    <div class="form-group col-md-12 d-none cheque_number required">
                        <label for="cheque_number">Cheque No.</label>
                        <input type="text" class="form-control" name="cheque_number" id="cheque_number">
                    </div>
                    <x-form.textarea labelName="Payment Note" name="payment_note" col="col-md-12"/>
                </div>
            </div>
            <!-- /modal body -->

            <!-- Modal Footer -->
            <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary btn-sm" id="payment-save-btn">Save</button>
            </div>
            <!-- /modal footer -->
        </form>
      </div>
      <!-- /modal content -->

    </div>
  </div>