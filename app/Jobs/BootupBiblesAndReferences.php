<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

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
        // First, migrate the Bible tables fresh
        Log::info('Starting Bible tables migration...');
        $this->migrateBibleTables();

        Log::info('Starting Bible and References bootup process...');

        // Then, install all Bibles
        InstallAllBibles::dispatchSync();

        // Finally, install references for the first Bible
        InstallReferencesForFirstBible::dispatchSync();

        Log::info('Bible and References bootup process completed.');
    }

    /**
     * Migrate fresh the Bible tables and their relations
     */
    private function migrateBibleTables(): void
    {
        try {
            Artisan::call('seed:admin');

            Log::info('Bible tables migrated fresh successfully.');
        } catch (\Exception $e) {
            Log::error('Error migrating Bible tables: '.$e->getMessage());
            throw $e;
        }
    }

}
