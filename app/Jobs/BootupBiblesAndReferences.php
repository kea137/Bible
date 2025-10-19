<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BootupBiblesAndReferences implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200; // 2 hours timeout for very large operations

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting Bible and References bootup process...');

        // Migrate fresh the bibles tables before running the jobs
        $this->migrateFreshBiblesTables();

        // First, install all Bibles
        InstallAllBibles::dispatchSync();

        // Then, install references for the first Bible
        InstallReferencesForFirstBible::dispatchSync();

        Log::info('Bible and References bootup process completed.');
    }

    /**
     * Migrate fresh the bibles tables (bibles, books, chapters, verses, references)
     * This drops and recreates only the Bible-related tables
     */
    private function migrateFreshBiblesTables(): void
    {
        Log::info('Migrating fresh bibles tables...');

        try {
            // Drop tables in the correct order (reverse of foreign key dependencies)
            $tablesToDrop = ['references', 'verses', 'chapters', 'books', 'bibles'];

            foreach ($tablesToDrop as $table) {
                if (Schema::hasTable($table)) {
                    Schema::dropIfExists($table);
                    Log::info("Dropped table: {$table}");
                }
            }

            // Run migrations to recreate the tables
            Artisan::call('migrate', [
                '--path' => 'database/migrations/2025_10_14_114100_create_bibles_table.php',
                '--force' => true,
            ]);

            Artisan::call('migrate', [
                '--path' => 'database/migrations/2025_10_15_114153_create_books_table.php',
                '--force' => true,
            ]);

            Artisan::call('migrate', [
                '--path' => 'database/migrations/2025_10_15_114311_create_chapters_table.php',
                '--force' => true,
            ]);

            Artisan::call('migrate', [
                '--path' => 'database/migrations/2025_10_15_114346_create_verses_table.php',
                '--force' => true,
            ]);

            Artisan::call('migrate', [
                '--path' => 'database/migrations/2025_10_15_115145_create_references_table.php',
                '--force' => true,
            ]);

            Log::info('Successfully migrated fresh bibles tables.');
        } catch (\Exception $e) {
            Log::error('Failed to migrate fresh bibles tables: '.$e->getMessage());
            throw $e;
        }
    }
}
