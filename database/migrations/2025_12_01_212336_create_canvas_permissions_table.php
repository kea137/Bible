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
        Schema::create('canvas_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canvas_id')->constrained('verse_link_canvases')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['owner', 'editor', 'viewer'])->default('viewer');
            $table->timestamps();

            // Ensure a user has only one permission per canvas
            $table->unique(['canvas_id', 'user_id']);
            
            // Add indexes for common queries
            $table->index('canvas_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canvas_permissions');
    }
};
