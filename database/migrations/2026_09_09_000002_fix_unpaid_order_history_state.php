<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereIn('status', ['pending', 'preparing', 'ready', 'served', 'saved'])
            ->update(['payment_status' => 'unpaid']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('orders')
            ->where('payment_status', 'unpaid')
            ->whereIn('status', ['pending', 'preparing', 'ready', 'served', 'saved'])
            ->update(['payment_status' => 'paid']);
    }
};
