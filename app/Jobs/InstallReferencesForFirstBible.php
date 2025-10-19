<?php

namespace App\Jobs;

use App\Models\Bible;
use App\Services\ReferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class InstallReferencesForFirstBible implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout for large operations

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
    public function handle(ReferenceService $referenceService): void
    {
        // Get the first Bible (by ID)
        $firstBible = Bible::orderBy('id', 'asc')->first();

        if (! $firstBible) {
            Log::error('No Bibles found in database. Cannot install references.');

            return;
        }

        Log::info("Installing references for first Bible: {$firstBible->abbreviation}");

        $referencesPath = resource_path('References');
        $files = File::files($referencesPath);

        $installedCount = 0;
        $errorCount = 0;

        foreach ($files as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            try {
                $filename = $file->getFilename();

                // Read and parse the reference file
                $data = json_decode(file_get_contents($file->getRealPath()), true);

                if (! $data) {
                    Log::error("Failed to parse JSON for {$filename}");
                    $errorCount++;
                    continue;
                }

                // Use ReferenceService to load references
                $referenceService->loadFromJson($firstBible, $data);

                Log::info("Successfully processed reference file: {$filename}");
                $installedCount++;
            } catch (\Exception $e) {
                Log::error("Error processing reference file {$file->getFilename()}: ".$e->getMessage());
                $errorCount++;
            }
        }

        Log::info("Reference installation complete. Installed: {$installedCount}, Errors: {$errorCount}");
    }
}
