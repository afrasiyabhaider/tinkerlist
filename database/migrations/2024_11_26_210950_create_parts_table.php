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
        // Create parts table
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('episode_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unique(['episode_id', 'title']); // Ensure unique title within the same episode
            $table->integer('position')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            // Add index for faster queries
            $table->index(['episode_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
