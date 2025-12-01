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
        // Add indexes to notes table for common queries
        Schema::table('notes', function (Blueprint $table) {
            $table->index('verse_id');
            $table->index('user_id');
            $table->index(['user_id', 'verse_id']);
        });

        // Add index to references table for verse lookups
        Schema::table('references', function (Blueprint $table) {
            $table->index('verse_id');
        });

        // Add index to verse_link_nodes for verse lookups
        Schema::table('verse_link_nodes', function (Blueprint $table) {
            $table->index('verse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropIndex(['verse_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['user_id', 'verse_id']);
        });

        Schema::table('references', function (Blueprint $table) {
            $table->dropIndex(['verse_id']);
        });

        Schema::table('verse_link_nodes', function (Blueprint $table) {
            $table->dropIndex(['verse_id']);
        });
    }
};
