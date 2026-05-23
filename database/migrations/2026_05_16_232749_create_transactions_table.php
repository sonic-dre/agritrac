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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('produce_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30); // purchase/expense/advance
            $table->decimal('quantity_kg', 10, 2)->nullable();
            $table->bigInteger('unit_price')->nullable();
            $table->bigInteger('total_amount'); // negative = outflow, positive = inflow
            $table->string('location')->nullable();
            $table->string('category')->nullable(); // fuel/labour/packaging/levies/maintenance
            $table->date('transaction_date');
            $table->string('sync_status', 30)->default('synced');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
