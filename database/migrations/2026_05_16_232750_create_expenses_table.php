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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 30); // fuel/labour/packaging/levies/maintenance
            $table->string('label');
            $table->string('sub_label')->nullable();
            $table->bigInteger('amount');
            $table->decimal('percentage', 5, 2)->default(0);
            $table->string('bar_color', 20)->default('#3fb950');
            $table->string('icon', 10)->nullable();
            $table->date('expense_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
