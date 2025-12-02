<?php

namespace App\Services;

use App\Models\MemoryVerse;
use Carbon\Carbon;

class SpacedRepetitionService
{
    /**
     * Calculate the next review date using SM-2 algorithm
     *
     * @param  int  $quality  Quality of recall (0-5, where 0 is complete blackout and 5 is perfect recall)
     *                        Values outside this range will be clamped to [0, 5]
     */
    public function updateReviewSchedule(MemoryVerse $memoryVerse, int $quality): void
    {
        // Clamp quality rating to valid range (0-5)
        // This ensures client bugs don't break the algorithm
        $quality = max(0, min(5, $quality));

        // Update review tracking
        $memoryVerse->total_reviews++;
        $memoryVerse->last_reviewed_at = Carbon::now();

        // If quality >= 3, it's a correct response
        if ($quality >= 3) {
            $memoryVerse->correct_reviews++;
        }

        // Update easiness factor
        $easinessFactor = $memoryVerse->easiness_factor;
        $easinessFactor = $easinessFactor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));

        // Easiness factor should not fall below 1.3
        $easinessFactor = max(1.3, $easinessFactor);
        $memoryVerse->easiness_factor = $easinessFactor;

        // Calculate new interval and repetitions
        if ($quality < 3) {
            // Incorrect response - reset repetitions and start over
            $memoryVerse->repetitions = 0;
            $memoryVerse->interval = 1;
        } else {
            // Correct response - increase interval based on repetitions
            $memoryVerse->repetitions++;

            if ($memoryVerse->repetitions == 1) {
                $memoryVerse->interval = 1;
            } elseif ($memoryVerse->repetitions == 2) {
                $memoryVerse->interval = 6;
            } else {
                $memoryVerse->interval = round($memoryVerse->interval * $easinessFactor);
            }
        }

        // Set next review date
        $memoryVerse->next_review_date = Carbon::now()->addDays($memoryVerse->interval);

        $memoryVerse->save();
    }

    /**
     * Initialize a new memory verse with default SM-2 values
     */
    public function createMemoryVerse(int $userId, int $verseId): MemoryVerse
    {
        return MemoryVerse::create([
            'user_id' => $userId,
            'verse_id' => $verseId,
            'repetitions' => 0,
            'easiness_factor' => 2.5,
            'interval' => 1,
            'next_review_date' => Carbon::now()->addDay(),
            'total_reviews' => 0,
            'correct_reviews' => 0,
        ]);
    }

    /**
     * Get all due memory verses for a user
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDueMemoryVerses(int $userId)
    {
        return MemoryVerse::with('verse.book', 'verse.chapter')
            ->where('user_id', $userId)
            ->where('next_review_date', '<=', Carbon::now()->toDateString())
            ->orderBy('next_review_date')
            ->get();
    }

    /**
     * Get statistics for user's memory verses
     */
    public function getStatistics(int $userId): array
    {
        $memoryVerses = MemoryVerse::where('user_id', $userId)->get();

        $totalVerses = $memoryVerses->count();
        $dueVerses = $memoryVerses->filter(fn ($mv) => $mv->isDue())->count();
        $totalReviews = $memoryVerses->sum('total_reviews');
        $correctReviews = $memoryVerses->sum('correct_reviews');
        $overallSuccessRate = $totalReviews > 0
            ? round(($correctReviews / $totalReviews) * 100, 2)
            : 0;

        return [
            'total_memory_verses' => $totalVerses,
            'due_for_review' => $dueVerses,
            'total_reviews' => $totalReviews,
            'correct_reviews' => $correctReviews,
            'overall_success_rate' => $overallSuccessRate,
        ];
    }
}
