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
        Schema::create('verse_link_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canvas_id')->constrained('verse_link_canvases')->onDelete('cascade');
            $table->foreignId('verse_id')->constrained()->onDelete('cascade');
            $table->integer('position_x')->default(0);
            $table->integer('position_y')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            // A verse can only appear once per canvas
            $table->unique(['canvas_id', 'verse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verse_link_nodes');
    }
};
