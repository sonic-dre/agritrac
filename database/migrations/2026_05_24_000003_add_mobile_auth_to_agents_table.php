<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('phone');
            $table->string('password')->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('password');
            $table->string('remember_token', 100)->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn(['email', 'password', 'is_active', 'remember_token']);
        });
    }
};
