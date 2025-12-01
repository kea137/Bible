<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Note;
use App\Models\Verse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Unified search across verses, notes, and lessons
     */
    public function search(Request $request)
    {
        $query = $request->input('query', '');
        $filters = $request->input('filters', []);
        
        // Handle types parameter - can be JSON string or array
        $types = $request->input('types', ['verses', 'notes', 'lessons']);
        if (is_string($types)) {
            $types = json_decode($types, true) ?: ['verses', 'notes', 'lessons'];
        }
        
        // Handle filters - can be JSON string or array
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?: [];
        }
        
        $perPage = min((int) $request->input('per_page', 10), 100);

        $results = [
            'query' => $query,
            'filters' => $filters,
            'verses' => [],
            'notes' => [],
            'lessons' => [],
        ];

        // Search verses
        if (in_array('verses', $types) && $query) {
            $versesQuery = Verse::search($query);

            // Apply filters
            if (!empty($filters['bible_id'])) {
                $versesQuery->where('bible_id', $filters['bible_id']);
            }
            if (!empty($filters['book_id'])) {
                $versesQuery->where('book_id', $filters['book_id']);
            }
            if (!empty($filters['version'])) {
                $versesQuery->where('version', $filters['version']);
            }
            if (!empty($filters['language'])) {
                $versesQuery->where('language', $filters['language']);
            }

            $results['verses'] = $versesQuery
                ->paginate($perPage)
                ->withQueryString()
                ->through(function ($verse) {
                    return [
                        'id' => $verse->id,
                        'text' => $verse->text,
                        'verse_number' => $verse->verse_number,
                        'book' => $verse->book?->title,
                        'chapter' => $verse->chapter?->chapter_number,
                        'version' => $verse->bible?->abbreviation,
                        'language' => $verse->bible?->language,
                    ];
                });
        }

        // Search notes (only user's own notes)
        if (in_array('notes', $types) && $query && auth()->check()) {
            $notesQuery = Note::search($query)
                ->where('user_id', auth()->id());

            // Apply date filter
            if (!empty($filters['date_from'])) {
                $notesQuery->where('created_at', '>=', strtotime($filters['date_from']));
            }
            if (!empty($filters['date_to'])) {
                $notesQuery->where('created_at', '<=', strtotime($filters['date_to']));
            }

            $results['notes'] = $notesQuery
                ->paginate($perPage)
                ->withQueryString()
                ->through(function ($note) {
                    return [
                        'id' => $note->id,
                        'title' => $note->title,
                        'content' => $note->content,
                        'verse_id' => $note->verse_id,
                        'created_at' => $note->created_at?->toIso8601String(),
                        'updated_at' => $note->updated_at?->toIso8601String(),
                    ];
                });
        }

        // Search lessons
        if (in_array('lessons', $types) && $query) {
            $lessonsQuery = Lesson::search($query);

            // Apply filters
            if (!empty($filters['language'])) {
                $lessonsQuery->where('language', $filters['language']);
            }
            if (!empty($filters['series_id'])) {
                $lessonsQuery->where('series_id', $filters['series_id']);
            }
            if (!empty($filters['date_from'])) {
                $lessonsQuery->where('created_at', '>=', strtotime($filters['date_from']));
            }
            if (!empty($filters['date_to'])) {
                $lessonsQuery->where('created_at', '<=', strtotime($filters['date_to']));
            }

            $results['lessons'] = $lessonsQuery
                ->paginate($perPage)
                ->withQueryString()
                ->through(function ($lesson) {
                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'description' => $lesson->description,
                        'language' => $lesson->language,
                        'series_id' => $lesson->series_id,
                        'episode_number' => $lesson->episode_number,
                        'created_at' => $lesson->created_at?->toIso8601String(),
                    ];
                });
        }

        return response()->json($results);
    }

    /**
     * Get available filter options
     */
    public function filterOptions(Request $request)
    {
        $options = [
            'bibles' => \App\Models\Bible::select('id', 'name', 'abbreviation', 'language')
                ->orderBy('name')
                ->get(),
            'books' => \App\Models\Book::select('id', 'title')
                ->orderBy('book_number')
                ->get(),
            'series' => \App\Models\LessonSeries::select('id', 'title')
                ->orderBy('title')
                ->get(),
            'languages' => \App\Models\Bible::select('language')
                ->distinct()
                ->orderBy('language')
                ->pluck('language'),
        ];

        return response()->json($options);
    }
}
