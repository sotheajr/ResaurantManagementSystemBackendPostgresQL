<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->bigIncrements('purchase_id');
            $table->unsignedBigInteger('supplier_id');
            $table->date('purchase_date')->nullable();
            $table->decimal('total', 10, 2);
            $table->timestamps();

            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};