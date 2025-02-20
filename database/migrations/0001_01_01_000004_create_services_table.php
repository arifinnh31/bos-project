<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('type')->default('Jasa')->nullable();
            $table->string('nama_jasa')->nullable();
            $table->integer('harga_beli')->nullable();
            $table->string('kategori_jasa')->nullable();
            $table->string('satuan_perhitungan')->nullable();
            $table->integer('harga_jual')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
