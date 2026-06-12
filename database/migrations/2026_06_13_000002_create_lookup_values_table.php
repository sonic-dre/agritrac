<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lookup_values', function (Blueprint $table) {
            $table->id();
            $table->string('group', 40);   // grade | payment_method | expense_category
            $table->string('label', 80);
            $table->string('value', 80);
            $table->string('emoji', 10)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default values
        $now = now();
        DB::table('lookup_values')->insert([
            // Grades
            ['group' => 'grade',            'label' => 'Grade A',               'value' => 'Grade A',               'emoji' => null, 'sort_order' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'grade',            'label' => 'Grade B',               'value' => 'Grade B',               'emoji' => null, 'sort_order' => 2, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'grade',            'label' => 'Grade C',               'value' => 'Grade C',               'emoji' => null, 'sort_order' => 3, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Payment methods
            ['group' => 'payment_method',   'label' => 'Cash',                  'value' => 'Cash',                  'emoji' => '💵', 'sort_order' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'payment_method',   'label' => 'Mobile Money',          'value' => 'Mobile Money',          'emoji' => '📱', 'sort_order' => 2, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'payment_method',   'label' => 'Bank Transfer',         'value' => 'Bank Transfer',         'emoji' => '🏦', 'sort_order' => 3, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'payment_method',   'label' => 'Cheque',                'value' => 'Cheque',                'emoji' => '📋', 'sort_order' => 4, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Expense categories
            ['group' => 'expense_category', 'label' => 'Fuel',                  'value' => 'Fuel',                  'emoji' => '⛽', 'sort_order' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'expense_category', 'label' => 'Driver Allowance',      'value' => 'Driver Allowance',      'emoji' => '🚗', 'sort_order' => 2, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'expense_category', 'label' => 'Porter / Loading',      'value' => 'Porter / Loading',      'emoji' => '👷', 'sort_order' => 3, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'expense_category', 'label' => 'Weigh Bridge',          'value' => 'Weigh Bridge',          'emoji' => '⚖️', 'sort_order' => 4, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'expense_category', 'label' => 'Market Levy',           'value' => 'Market Levy',           'emoji' => '🏛️', 'sort_order' => 5, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'expense_category', 'label' => 'Vehicle Repair',        'value' => 'Vehicle Repair',        'emoji' => '🔧', 'sort_order' => 6, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'expense_category', 'label' => 'Food / Accommodation',  'value' => 'Food / Accommodation',  'emoji' => '🍽️', 'sort_order' => 7, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'expense_category', 'label' => 'Other',                 'value' => 'Other',                 'emoji' => '💸', 'sort_order' => 8, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('lookup_values');
    }
};
