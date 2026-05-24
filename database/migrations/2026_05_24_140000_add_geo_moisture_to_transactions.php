<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('notes');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->decimal('moisture_content', 5, 2)->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'moisture_content']);
        });
    }
};
