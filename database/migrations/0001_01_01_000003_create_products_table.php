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
        Schema::create('products', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 300);
            $table->string('spu', 256)->nullable();
            $table->string('full_category_id')->nullable();
            $table->string('full_category_name')->nullable();
            $table->string('brand', 20)->nullable();
            $table->string('sale_status')->default('FOR_SALE')->nullable();
            $table->enum('condition', ['NEW', 'USED'])->default('NEW')->nullable();;
            $table->boolean('has_shelf_life')->default(false);
            $table->integer('shelf_life_duration')->nullable();
            $table->integer('inbound_limit')->nullable();
            $table->integer('outbound_limit')->nullable();
            $table->integer('min_purchase')->default(1);
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->boolean('has_variations')->default(false);
            $table->json('variant_options')->nullable();
            $table->json('images')->nullable();
            $table->integer('length')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('weight')->nullable();
            $table->boolean('preorder')->default(false);
            $table->integer('preorder_duration')->nullable();
            $table->string('preorder_unit')->nullable();
            $table->string('customs_chinese_name', 200)->nullable();
            $table->string('customs_english_name', 200)->nullable();
            $table->string('hs_code', 200)->nullable();
            $table->decimal('invoice_amount', 12, 2)->nullable();
            $table->integer('gross_weight')->nullable();
            $table->string('source_url', 150)->nullable();
            $table->integer('purchase_duration')->nullable();
            $table->string('purchase_unit')->nullable();
            $table->decimal('sales_tax_amount', 12, 2)->nullable();
            $table->string('remarks1', 50)->nullable();
            $table->string('remarks2', 50)->nullable();
            $table->string('remarks3', 50)->nullable();
            $table->integer('sold')->default(0);
            $table->integer('review')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
