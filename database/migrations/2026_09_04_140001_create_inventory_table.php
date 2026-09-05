<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->bigIncrements('inventory_id');
            $table->string('ingredient_name', 100);
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit', 20)->nullable();
            $table->decimal('minimum_stock', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};