<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id('role_id');
            $table->string('role_name', 30)->unique();
            $table->string('role_name_kh', 255)->nullable();
            $table->timestamps();
        });

        // 2. Role Permissions table
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id('permission_id');
            $table->foreignId('role_id')->constrained('roles', 'role_id')->onDelete('cascade');
            $table->string('permission_name', 50);
            $table->boolean('can_access')->default(false);
            $table->unique(['role_id', 'permission_name']);
            $table->timestamps();
        });

        // 3. Users table
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->foreignId('role_id')->constrained('roles', 'role_id');
            $table->string('full_name', 100);
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->string('gender', 10)->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->timestamp('hire_date')->nullable();
            $table->string('status', 20)->default('Active');
            $table->string('image', 255)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
    }
};
