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
        Schema::create('verse_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('verse_id')->constrained()->onDelete('cascade');
            $table->float('score')->default(0);
            $table->json('reasons')->nullable();
            $table->boolean('dismissed')->default(false);
            $table->boolean('clicked')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'score']);
            $table->index(['user_id', 'dismissed', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verse_suggestions');
    }
};
