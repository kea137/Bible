<?php

namespace App\Services;

use App\Models\Note;
use App\Models\ReadingProgress;
use App\Models\User;
use App\Models\Verse;
use App\Models\VerseHighlight;
use App\Models\VerseSuggestion;
use Illuminate\Support\Facades\DB;

class VerseSuggestionService
{
    /**
     * Generate verse suggestions for a user based on their history and highlights
     */
    public function generateSuggestionsForUser(User $user, int $limit = 10): array
    {
        $suggestions = [];

        // Get user's highlighted verses
        $highlightedVerses = $this->getUserHighlightedVerses($user);

        // Get user's notes on verses
        $notedVerses = $this->getUserNotedVerses($user);

        // Get user's reading progress
        $readChapters = $this->getUserReadChapters($user);

        // Generate suggestions based on cross-references from highlighted verses
        $crossRefSuggestions = $this->getSuggestionsFromCrossReferences($user, $highlightedVerses);
        $suggestions = array_merge($suggestions, $crossRefSuggestions);

        // Generate suggestions based on same book/chapter context
        $contextualSuggestions = $this->getContextualSuggestions($user, $highlightedVerses, $notedVerses);
        $suggestions = array_merge($suggestions, $contextualSuggestions);

        // Generate suggestions based on similar topics (keyword matching)
        $topicalSuggestions = $this->getTopicalSuggestions($user, $highlightedVerses, $notedVerses);
        $suggestions = array_merge($suggestions, $topicalSuggestions);

        // Sort by score and limit
        usort($suggestions, fn ($a, $b) => $b['score'] <=> $a['score']);
        $suggestions = array_slice($suggestions, 0, $limit);

        // Store suggestions in database
        $this->storeSuggestions($user, $suggestions);

        return $suggestions;
    }

    /**
     * Get user's highlighted verses
     */
    private function getUserHighlightedVerses(User $user): array
    {
        return VerseHighlight::where('user_id', $user->id)
            ->with('verse')
            ->get()
            ->pluck('verse')
            ->toArray();
    }

    /**
     * Get user's verses with notes
     */
    private function getUserNotedVerses(User $user): array
    {
        return Note::where('user_id', $user->id)
            ->whereNotNull('verse_id')
            ->with('verse')
            ->get()
            ->pluck('verse')
            ->toArray();
    }

    /**
     * Get user's read chapters
     */
    private function getUserReadChapters(User $user): array
    {
        return ReadingProgress::where('user_id', $user->id)
            ->where('is_read', true)
            ->pluck('chapter_id')
            ->toArray();
    }

    /**
     * Get suggestions based on cross-references from user's highlighted verses
     */
    private function getSuggestionsFromCrossReferences(User $user, array $highlightedVerses): array
    {
        $suggestions = [];

        foreach ($highlightedVerses as $verse) {
            if (! isset($verse['id'])) {
                continue;
            }

            // Get cross-references for this verse
            $references = DB::table('references')
                ->where('verse_id', $verse['id'])
                ->get();

            foreach ($references as $ref) {
                if (! $ref->to_verse_id) {
                    continue;
                }

                // Check if user already highlighted this verse
                $isHighlighted = VerseHighlight::where('user_id', $user->id)
                    ->where('verse_id', $ref->to_verse_id)
                    ->exists();

                if ($isHighlighted) {
                    continue;
                }

                // Get the referenced verse
                $refVerse = Verse::with(['book', 'chapter', 'bible'])->find($ref->to_verse_id);
                if (! $refVerse) {
                    continue;
                }

                $suggestions[] = [
                    'verse_id' => $refVerse->id,
                    'verse' => $refVerse,
                    'score' => 0.8,
                    'reasons' => [
                        [
                            'type' => 'cross_reference',
                            'description' => 'Related to a verse you highlighted',
                            'source_verse' => $this->formatVerseReference($verse),
                        ],
                    ],
                ];
            }
        }

        return $suggestions;
    }

    /**
     * Get contextual suggestions from same books/chapters
     */
    private function getContextualSuggestions(User $user, array $highlightedVerses, array $notedVerses): array
    {
        $suggestions = [];
        $allVerses = array_merge($highlightedVerses, $notedVerses);

        // Get unique book IDs
        $bookIds = array_unique(array_column($allVerses, 'book_id'));

        foreach ($bookIds as $bookId) {
            // Get verses from same book that user hasn't highlighted
            $verses = Verse::where('book_id', $bookId)
                ->whereNotIn('id', array_column($highlightedVerses, 'id'))
                ->whereNotIn('id', array_column($notedVerses, 'id'))
                ->with(['book', 'chapter', 'bible'])
                ->inRandomOrder()
                ->limit(3)
                ->get();

            foreach ($verses as $verse) {
                $suggestions[] = [
                    'verse_id' => $verse->id,
                    'verse' => $verse,
                    'score' => 0.5,
                    'reasons' => [
                        [
                            'type' => 'same_book',
                            'description' => 'From the book of '.$verse->book->title.' which you\'ve been reading',
                        ],
                    ],
                ];
            }
        }

        return $suggestions;
    }

    /**
     * Get topical suggestions based on keyword matching
     */
    private function getTopicalSuggestions(User $user, array $highlightedVerses, array $notedVerses): array
    {
        $suggestions = [];

        // Extract keywords from highlighted verses
        $keywords = $this->extractKeywords($highlightedVerses);

        if (empty($keywords)) {
            return $suggestions;
        }

        // Search for verses with similar keywords
        $highlightedIds = array_column($highlightedVerses, 'id');
        $notedIds = array_column($notedVerses, 'id');
        $excludedIds = array_unique(array_merge($highlightedIds, $notedIds));

        foreach ($keywords as $keyword) {
            $verses = Verse::where('text', 'LIKE', '%'.$keyword.'%')
                ->whereNotIn('id', $excludedIds)
                ->with(['book', 'chapter', 'bible'])
                ->inRandomOrder()
                ->limit(2)
                ->get();

            foreach ($verses as $verse) {
                $suggestions[] = [
                    'verse_id' => $verse->id,
                    'verse' => $verse,
                    'score' => 0.6,
                    'reasons' => [
                        [
                            'type' => 'keyword_match',
                            'description' => 'Contains similar themes to verses you\'ve highlighted',
                            'keyword' => $keyword,
                        ],
                    ],
                ];
            }
        }

        return $suggestions;
    }

    /**
     * Extract important keywords from verses
     */
    private function extractKeywords(array $verses): array
    {
        $keywords = [];
        $commonWords = ['the', 'and', 'of', 'to', 'a', 'in', 'that', 'is', 'for', 'it', 'with', 'as', 'was', 'on', 'be', 'at', 'by', 'from', 'or', 'an', 'this', 'not', 'but', 'they', 'you', 'he', 'she', 'will', 'have', 'are'];

        foreach ($verses as $verse) {
            if (! isset($verse['text'])) {
                continue;
            }

            $words = preg_split('/\s+/', strtolower($verse['text']));
            foreach ($words as $word) {
                $word = preg_replace('/[^a-z]/', '', $word);
                if (strlen($word) > 4 && ! in_array($word, $commonWords)) {
                    $keywords[] = $word;
                }
            }
        }

        // Get most common keywords
        $keywords = array_count_values($keywords);
        arsort($keywords);

        return array_slice(array_keys($keywords), 0, 10);
    }

    /**
     * Store suggestions in database
     */
    private function storeSuggestions(User $user, array $suggestions): void
    {
        // Clear old suggestions (older than 7 days)
        VerseSuggestion::where('user_id', $user->id)
            ->where('created_at', '<', now()->subDays(7))
            ->delete();

        foreach ($suggestions as $suggestion) {
            VerseSuggestion::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'verse_id' => $suggestion['verse_id'],
                ],
                [
                    'score' => $suggestion['score'],
                    'reasons' => $suggestion['reasons'],
                ]
            );
        }
    }

    /**
     * Format a verse reference for display
     */
    private function formatVerseReference($verse): string
    {
        if (! isset($verse['book_id'])) {
            return 'Unknown';
        }

        $book = DB::table('books')->find($verse['book_id']);
        $chapter = DB::table('chapters')->find($verse['chapter_id']);

        if (! $book || ! $chapter) {
            return 'Unknown';
        }

        return $book->title.' '.$chapter->chapter_number.':'.($verse['verse_number'] ?? '?');
    }

    /**
     * Get active suggestions for a user
     */
    public function getActiveSuggestions(User $user, int $limit = 10)
    {
        return VerseSuggestion::where('user_id', $user->id)
            ->where('dismissed', false)
            ->where('created_at', '>', now()->subDays(7))
            ->with(['verse.book', 'verse.chapter', 'verse.bible'])
            ->orderBy('score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark a suggestion as clicked
     */
    public function markAsClicked(VerseSuggestion $suggestion): void
    {
        $suggestion->update(['clicked' => true]);
    }

    /**
     * Mark a suggestion as dismissed
     */
    public function markAsDismissed(VerseSuggestion $suggestion): void
    {
        $suggestion->update(['dismissed' => true]);
    }
}
