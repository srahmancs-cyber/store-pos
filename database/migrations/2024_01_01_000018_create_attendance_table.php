<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->timestamp('clock_in');
            $table->timestamp('clock_out')->nullable();
            $table->date('date');
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
