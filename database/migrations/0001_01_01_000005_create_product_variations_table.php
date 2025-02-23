<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('product_variations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('ginee_id')->nullable();
            $table->string('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('name');
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('price', 12, 2)->default(1)->nullable();
            $table->integer('stock');
            $table->string('msku');
            $table->string('barcode')->nullable();
            $table->json('combinations')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
