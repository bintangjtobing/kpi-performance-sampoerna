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
        Schema::create('progress_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_progress_id')->constrained('daily_progress')->onDelete('cascade');
            $table->string('item_name');
            $table->integer('target_value');
            $table->integer('actual_value');
            $table->decimal('percentage', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_items');
    }
};