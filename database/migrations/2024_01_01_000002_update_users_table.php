<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'manager', 'cashier'])->default('cashier')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
            $table->integer('failed_login_attempts')->default(0)->after('is_active');
            $table->timestamp('locked_at')->nullable()->after('failed_login_attempts');
            $table->string('phone')->nullable()->after('name');
            $table->date('hire_date')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active', 'failed_login_attempts', 'locked_at', 'phone', 'hire_date']);
        });
    }
};
