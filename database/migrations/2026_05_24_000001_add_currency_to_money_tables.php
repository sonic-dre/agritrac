<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->string('currency', 10)->default('UGX')->after('payment_type');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('currency', 10)->default('UGX')->after('price_per_kg');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->string('currency', 10)->default('UGX')->after('amount');
        });

        Schema::table('price_records', function (Blueprint $table) {
            $table->string('currency', 10)->default('UGX')->after('price_per_kg');
        });
    }

    public function down(): void
    {
        Schema::table('trips', fn (Blueprint $t) => $t->dropColumn('currency'));
        Schema::table('transactions', fn (Blueprint $t) => $t->dropColumn('currency'));
        Schema::table('expenses', fn (Blueprint $t) => $t->dropColumn('currency'));
        Schema::table('price_records', fn (Blueprint $t) => $t->dropColumn('currency'));
    }
};
