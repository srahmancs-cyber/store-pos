<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capital_injections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('amount');
            $table->enum('source_type', ['owner', 'external']);
            $table->unsignedBigInteger('source_id')->nullable(); // owner_id if source_type = 'owner'
            $table->enum('destination_type', ['cash', 'bank']);
            $table->text('purpose')->nullable();
            $table->date('transaction_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('source_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capital_injections');
    }
};
