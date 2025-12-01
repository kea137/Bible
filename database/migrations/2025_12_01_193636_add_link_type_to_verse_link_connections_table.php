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
        Schema::table('verse_link_connections', function (Blueprint $table) {
            $table->string('link_type')->default('general')->after('target_node_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verse_link_connections', function (Blueprint $table) {
            $table->dropColumn('link_type');
        });
    }
};
