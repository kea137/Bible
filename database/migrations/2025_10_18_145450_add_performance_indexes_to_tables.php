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
        // Add indexes to bibles table for language filtering
        Schema::table('bibles', function (Blueprint $table) {
            $table->index('language');
        });

        // Add indexes to reading_progress table for common queries
        Schema::table('reading_progress', function (Blueprint $table) {
            $table->index('completed');
            $table->index('completed_at');
            $table->index(['user_id', 'completed']);
            $table->index(['user_id', 'completed_at']);
        });

        // Add composite indexes to verses for better query performance
        Schema::table('verses', function (Blueprint $table) {
            $table->index(['chapter_id', 'verse_number']);
            $table->index(['book_id', 'chapter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bibles', function (Blueprint $table) {
            $table->dropIndex(['language']);
        });

        Schema::table('reading_progress', function (Blueprint $table) {
            $table->dropIndex(['completed']);
            $table->dropIndex(['completed_at']);
            $table->dropIndex(['user_id', 'completed']);
            $table->dropIndex(['user_id', 'completed_at']);
        });

        Schema::table('verses', function (Blueprint $table) {
            $table->dropIndex(['chapter_id', 'verse_number']);
            $table->dropIndex(['book_id', 'chapter_id']);
        });
    }
};
