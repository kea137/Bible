<?php

namespace App\Jobs;

use App\Models\Bible;
use App\Models\Notification;
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
        public array $data,
        public int $userId
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

            // Create success notification
            Notification::create([
                'user_id' => $this->userId,
                'type' => 'bible_upload_success',
                'title' => 'Bible Created Successfully',
                'message' => "{$this->bible->name} has been successfully created and is now available.",
                'data' => json_encode(['bible_id' => $this->bible->id, 'bible_name' => $this->bible->name]),
                'read' => false,
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

            // Create failure notification
            Notification::create([
                'user_id' => $this->userId,
                'type' => 'bible_upload_failed',
                'title' => 'Bible Creation Failed',
                'message' => "Failed to create {$this->bible->name}: {$e->getMessage()}",
                'data' => json_encode(['bible_id' => $this->bible->id, 'bible_name' => $this->bible->name, 'error' => $e->getMessage()]),
                'read' => false,
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

        // Create failure notification
        Notification::create([
            'user_id' => $this->userId,
            'type' => 'bible_upload_failed',
            'title' => 'Bible Creation Failed',
            'message' => "Failed to create {$this->bible->name}: {$exception->getMessage()}",
            'data' => json_encode(['bible_id' => $this->bible->id, 'bible_name' => $this->bible->name, 'error' => $exception->getMessage()]),
            'read' => false,
        ]);

        Log::error("Bible upload job failed for '{$this->bible->name}'.", [
            'bible_id' => $this->bible->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
