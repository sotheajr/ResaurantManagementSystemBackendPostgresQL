<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('inventory_id');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            $table->foreign('purchase_id')->references('purchase_id')->on('purchases')->onDelete('cascade');
            $table->foreign('inventory_id')->references('inventory_id')->on('inventory')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};