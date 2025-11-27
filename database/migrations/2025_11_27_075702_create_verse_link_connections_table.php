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
        Schema::create('verse_link_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canvas_id')->constrained('verse_link_canvases')->onDelete('cascade');
            $table->foreignId('source_node_id')->constrained('verse_link_nodes')->onDelete('cascade');
            $table->foreignId('target_node_id')->constrained('verse_link_nodes')->onDelete('cascade');
            $table->text('label')->nullable();
            $table->timestamps();

            // Prevent duplicate connections (in either direction)
            $table->unique(['canvas_id', 'source_node_id', 'target_node_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verse_link_connections');
    }
};
