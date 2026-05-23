<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->integer('negotiated_price_per_kg')->nullable()->after('advance_amount');
            $table->string('payment_type')->default('advance')->after('negotiated_price_per_kg');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['negotiated_price_per_kg', 'payment_type']);
        });
    }
};
