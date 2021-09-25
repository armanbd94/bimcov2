<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->string('refrence_no')->nullable();
            $table->string('batch_no')->unique();
            $table->date('mfg_date');
            $table->date('exp_date');
            $table->date('date');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedBigInteger('formulation_id');
            $table->foreign('formulation_id')->references('id')->on('formulations');
            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->double('total_fg_qty');
            $table->double('total_cost');
            $table->double('extra_cost')->nullable();
            $table->double('total_net_cost');
            $table->double('per_unit_cost');
            $table->unsignedBigInteger('pwarehouse_id')->comment('Product Warehouse');
            $table->foreign('pwarehouse_id')->references('id')->on('warehouses');
            $table->unsignedBigInteger('mwarehouse_id')->comment('Material Warehouse');
            $table->foreign('mwarehouse_id')->references('id')->on('warehouses');
            $table->text('note')->nullable();
            $table->enum('status',['1','2'])->default('2')->comment = "1=Approved, 2=Pending";
            $table->enum('production_status',['1','2'])->default('1')->comment = "1=Processing,2=Finished";
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
        Schema::dropIfExists('productions');
    }
}
