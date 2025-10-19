<?php

namespace App\Jobs;

use App\Models\Bible;
use App\Services\BibleJsonParser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class InstallAllBibles implements ShouldQueue
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
    public function handle(BibleJsonParser $parser): void
    {
        $biblesPath = resource_path('Bibles');
        $files = File::files($biblesPath);

        $installedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($files as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            try {
                $filename = $file->getFilename();
                // Extract Bible abbreviation from filename (e.g., "KJV_bible.json" -> "KJV")
                $abbreviation = str_replace('_bible.json', '', $filename);

                // Check if Bible already exists
                if (Bible::where('abbreviation', $abbreviation)->exists()) {
                    Log::info("Bible {$abbreviation} already exists, skipping.");
                    $skippedCount++;
                    continue;
                }

                // Read and parse the Bible file
                $data = json_decode(file_get_contents($file->getRealPath()), true);

                if (! $data) {
                    Log::error("Failed to parse JSON for {$filename}");
                    $errorCount++;
                    continue;
                }

                // Create Bible record with basic info
                // Use abbreviation as name for now, can be updated later
                $bible = Bible::create([
                    'name' => $abbreviation,
                    'abbreviation' => $abbreviation,
                    'language' => $this->detectLanguage($abbreviation),
                    'version' => '1.0',
                    'description' => "Automatically installed from {$filename}",
                ]);

                // Parse and store the Bible content
                $parser->parse($bible, $data);

                Log::info("Successfully installed Bible: {$abbreviation}");
                $installedCount++;
            } catch (\Exception $e) {
                Log::error("Error installing Bible from {$file->getFilename()}: ".$e->getMessage());
                $errorCount++;
            }
        }

        Log::info("Bible installation complete. Installed: {$installedCount}, Skipped: {$skippedCount}, Errors: {$errorCount}");
    }

    /**
     * Detect language from Bible abbreviation
     */
    private function detectLanguage(string $abbreviation): string
    {
        // Default to English for common English translations
        $englishBibles = ['KJV', 'NKJV', 'NIV', 'NIVUK', 'ESV', 'ESVUK', 'NASB', 'NLT', 'CSB', 'AMP', 'ASV', 'WEB', 'YLT', 'GNV', 'MEV', 'LEB', 'GW', 'NET', 'ISV', 'NRSV', 'NRSVUE', 'JUB', 'KJ21', 'AKJV', 'BRG', 'EHV', 'NLV', 'NOG', 'NASB1995'];

        if (in_array($abbreviation, $englishBibles)) {
            return 'English';
        }

        // Add other language detection logic here as needed
        return 'English'; // Default to English
    }
}
