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
        Schema::create('produce_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('emoji', 10);
            $table->string('slug', 30)->unique();
            $table->bigInteger('current_price');
            $table->decimal('change_percent', 5, 2)->default(0);
            $table->string('signal', 20)->default('hold'); // buy/hold/sell
            $table->string('primary_location')->nullable();
            $table->string('accent_color', 20)->default('#3fb950');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produce_types');
    }
};
