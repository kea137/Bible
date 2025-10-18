<?php

namespace App\Jobs;

use App\Models\Bible;
use App\Models\Notification;
use App\Models\User;
use App\Services\ReferenceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessReferencesUpload implements ShouldQueue
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
    public function handle(ReferenceService $referenceService): void
    {
        try {
            // Process the references
            $referenceService->loadFromJson($this->bible, $this->data);

            // Create success notification
            Notification::create([
                'user_id' => $this->userId,
                'type' => 'reference_upload_success',
                'title' => 'References Created Successfully',
                'message' => "References for {$this->bible->name} have been successfully created.",
                'data' => json_encode(['bible_id' => $this->bible->id, 'bible_name' => $this->bible->name]),
                'read' => false,
            ]);

            Log::info("References for Bible '{$this->bible->name}' uploaded successfully.", [
                'bible_id' => $this->bible->id,
                'user_id' => $this->userId,
            ]);
        } catch (\Exception $e) {
            // Create failure notification
            Notification::create([
                'user_id' => $this->userId,
                'type' => 'reference_upload_failed',
                'title' => 'References Creation Failed',
                'message' => "Failed to create references for {$this->bible->name}: {$e->getMessage()}",
                'data' => json_encode(['bible_id' => $this->bible->id, 'bible_name' => $this->bible->name, 'error' => $e->getMessage()]),
                'read' => false,
            ]);

            Log::error("Failed to process references for Bible '{$this->bible->name}'.", [
                'bible_id' => $this->bible->id,
                'user_id' => $this->userId,
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
        // Create failure notification
        Notification::create([
            'user_id' => $this->userId,
            'type' => 'reference_upload_failed',
            'title' => 'References Creation Failed',
            'message' => "Failed to create references for {$this->bible->name}: {$exception->getMessage()}",
            'data' => json_encode(['bible_id' => $this->bible->id, 'bible_name' => $this->bible->name, 'error' => $exception->getMessage()]),
            'read' => false,
        ]);

        Log::error("References upload job failed for '{$this->bible->name}'.", [
            'bible_id' => $this->bible->id,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}
