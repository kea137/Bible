<?php

namespace App\Jobs;

use App\Models\Bible;
use App\Services\BibleJsonParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessBibleUpload implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Bible $bible,
        public array $data
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(BibleJsonParser $parser): void
    {
        try {
            // Update status to processing
            $this->bible->update(['status' => 'processing']);

            // Parse the Bible data
            $parser->parse($this->bible, $this->data);

            // Update status to completed
            $this->bible->update([
                'status' => 'completed',
                'error_message' => null,
            ]);

            Log::info("Bible '{$this->bible->name}' uploaded successfully.", [
                'bible_id' => $this->bible->id,
            ]);
        } catch (\Exception $e) {
            // Update status to failed with error message
            $this->bible->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Failed to process Bible '{$this->bible->name}'.", [
                'bible_id' => $this->bible->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw the exception so the job will be marked as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Update the Bible status to failed
        $this->bible->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);

        Log::error("Bible upload job failed for '{$this->bible->name}'.", [
            'bible_id' => $this->bible->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
