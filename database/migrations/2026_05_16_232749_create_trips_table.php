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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->string('region');
            $table->json('produce_list')->nullable();
            $table->date('start_date');
            $table->integer('total_days');
            $table->integer('current_day')->default(0);
            $table->string('status', 30)->default('in_progress'); // in_progress/returning/departing/completed
            $table->string('sync_status', 30)->default('synced'); // synced/pending/offline
            $table->integer('offline_hours')->default(0);
            $table->integer('unsynced_records')->default(0);
            $table->decimal('tonnage_kg', 10, 2)->default(0);
            $table->bigInteger('amount_spent')->default(0);
            $table->bigInteger('advance_amount')->default(0);
            $table->bigInteger('revenue')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
