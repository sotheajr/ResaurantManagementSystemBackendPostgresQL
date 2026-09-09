<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'checkout_by')) {
                $table->foreignId('checkout_by')->nullable()->after('cashier_id')->constrained('users', 'user_id')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'checkout_by')) {
                $table->dropConstrainedForeignId('checkout_by');
            }
        });
    }
};
