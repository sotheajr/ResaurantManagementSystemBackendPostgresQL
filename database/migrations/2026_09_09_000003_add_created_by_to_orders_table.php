<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('user_id')->constrained('users', 'user_id')->nullOnDelete();
            }
        });

        DB::table('orders')
            ->whereNull('created_by')
            ->whereNotNull('user_id')
            ->update(['created_by' => DB::raw('user_id')]);

        DB::table('orders')
            ->whereNull('created_by')
            ->whereNull('user_id')
            ->update(['created_by' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};
