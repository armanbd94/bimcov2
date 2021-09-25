<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->string('code')->unique();
            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->unsignedBigInteger('sale_unit_id');
            $table->foreign('sale_unit_id')->references('id')->on('units');
            $table->string('cost');
            $table->string('price');
            $table->double('qty')->nullable();
            $table->double('alert_quantity')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->foreign('tax_id')->references('id')->on('taxes');
            $table->enum('tax_method',['1','2'])->comment = "1=Exclusive, 2=Inclusive";
            $table->enum('status',['1','2'])->default('1')->comment = "1=Active, 2=Inactive";
            $table->enum('has_opening_stock',['1','2'])->default('2')->comment = "1=Yes, 2=No";
            $table->double('opening_stock_qty')->nullable();
            $table->unsignedBigInteger('opening_warehouse_id')->nullable();
            $table->foreign('opening_warehouse_id')->references('id')->on('warehouses');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('products');
    }
}
