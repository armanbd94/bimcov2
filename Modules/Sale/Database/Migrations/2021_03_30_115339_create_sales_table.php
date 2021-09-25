<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->string('work_order_no')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->float('item');
            $table->float('total_qty');
            $table->double('total_discount');
            $table->double('total_tax');
            $table->double('total_labor_cost');
            $table->double('total_price');
            $table->double('order_tax_rate')->nullable();
            $table->double('order_tax')->nullable();
            $table->double('order_discount')->nullable();
            $table->double('shipping_cost')->nullable();
            $table->double('grand_total');
            $table->double('paid_amount')->nullable();
            $table->double('due_amount')->nullable();
            $table->double('previous_due')->nullable();
            $table->enum('payment_status',['1','2','3'])->comment="1=Paid,2=Partial,3=Due";
            $table->enum('payment_method',['1','2','3'])->nullable()->comment="1=Cash,2=Bank Deposit,3=Mobile";
            $table->unsignedBigInteger('account_id')->nullable();
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
            $table->string('document')->nullable();
            $table->text('note')->nullable();
            $table->date('sale_date');
            $table->enum('delivery_status',['1','2','3'])->nullable()->comment="1=Packaging,2=Delivering,3=Delivered";
            $table->date('delivery_date')->nullable();
            $table->enum('status',['1','2','3'])->default(3)->comment="1=Approved,2=Rejected,3=Pending";
            $table->string('status_change_by')->nullable();
            $table->dateTime('status_change_date')->nullable();
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
