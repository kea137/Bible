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
        // Add collaboration fields to verse_link_canvases
        Schema::table('verse_link_canvases', function (Blueprint $table) {
            $table->boolean('is_collaborative')->default(false)->after('description');
        });

        // Add version tracking to verse_link_nodes for conflict detection
        Schema::table('verse_link_nodes', function (Blueprint $table) {
            $table->integer('version')->default(1)->after('note');
            $table->foreignId('last_modified_by')->nullable()->constrained('users')->after('version');
            $table->timestamp('last_modified_at')->nullable()->after('last_modified_by');
        });

        // Add version tracking to verse_link_connections for conflict detection
        Schema::table('verse_link_connections', function (Blueprint $table) {
            $table->integer('version')->default(1)->after('label');
            $table->foreignId('last_modified_by')->nullable()->constrained('users')->after('version');
            $table->timestamp('last_modified_at')->nullable()->after('last_modified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verse_link_canvases', function (Blueprint $table) {
            $table->dropColumn('is_collaborative');
        });

        Schema::table('verse_link_nodes', function (Blueprint $table) {
            $table->dropForeign(['last_modified_by']);
            $table->dropColumn(['version', 'last_modified_by', 'last_modified_at']);
        });

        Schema::table('verse_link_connections', function (Blueprint $table) {
            $table->dropForeign(['last_modified_by']);
            $table->dropColumn(['version', 'last_modified_by', 'last_modified_at']);
        });
    }
};
