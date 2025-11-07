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
        Schema::table('users', function (Blueprint $table) {
            $table->json('preferred_translations')->nullable()->after('language');
            $table->json('appearance_preferences')->nullable()->after('preferred_translations');
            $table->boolean('onboarding_completed')->default(false)->after('appearance_preferences');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['preferred_translations', 'appearance_preferences', 'onboarding_completed']);
        });
    }
};
