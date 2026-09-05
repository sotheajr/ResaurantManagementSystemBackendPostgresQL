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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->bigIncrements('payroll_id');
            // The users table uses user_id as its primary key (not id)
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->string('month_year', 7); // format 'YYYY-MM'
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->integer('total_present_days')->default(0);
            $table->integer('total_absent_days')->default(0);
            $table->decimal('total_ot_hours', 5, 2)->default(0);
            $table->decimal('ot_amount', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->string('payment_status', 20)->default('Pending'); // Pending, Paid
            $table->date('payment_date')->nullable();
            $table->timestamps();

            // One payroll record per user per month
            $table->unique(['user_id', 'month_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};