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
        Schema::create('price_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produce_type_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('price_per_kg');
            $table->string('location');
            $table->string('period_label', 20); // "Jun 24", "Jul", etc.
            $table->date('recorded_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_records');
    }
};
