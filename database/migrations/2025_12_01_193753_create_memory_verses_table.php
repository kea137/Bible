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
        Schema::create('memory_verses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('verse_id')->constrained()->onDelete('cascade');

            // SM-2 algorithm fields
            $table->integer('repetitions')->default(0); // Number of correct reviews
            $table->float('easiness_factor', 8, 2)->default(2.5); // Easiness factor (1.3-2.5+)
            $table->integer('interval')->default(0); // Interval in days
            $table->date('next_review_date'); // Next scheduled review date
            $table->date('last_reviewed_at')->nullable(); // Last review date

            // Performance tracking
            $table->integer('total_reviews')->default(0); // Total number of reviews
            $table->integer('correct_reviews')->default(0); // Number of correct reviews

            $table->timestamps();

            // Ensure a user can only have one memory verse entry per verse
            $table->unique(['user_id', 'verse_id']);

            // Index for finding due reviews
            $table->index(['user_id', 'next_review_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memory_verses');
    }
};
