<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BootupBiblesAndReferences implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 72000; // 20 hours timeout for very large operations (this depends on server performance {30min for 8gb of ram})

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

        // Then, install all Bibles
        InstallAllBibles::dispatchSync();

        // Finally, install references for the first Bible
        InstallReferencesForFirstBible::dispatchSync();

        Log::info('Bible and References bootup process completed.');
    }

}
