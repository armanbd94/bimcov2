<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreProductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pre_productions', function (Blueprint $table) {
            $table->id();
            $table->string('serial_no')->unique();
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
            $table->unsignedBigInteger('mwarehouse_id')->comment('Material Warehouse');
            $table->foreign('mwarehouse_id')->references('id')->on('warehouses');
            $table->enum('status',['1','2'])->default('2')->comment = "1=Converted, 2=Pending";
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
        Schema::dropIfExists('pre_productions');
    }
}
