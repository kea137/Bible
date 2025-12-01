<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use ZipArchive;

class DataExportController extends Controller
{
    /**
     * Export user data as JSON/ZIP.
     */
    public function export(Request $request)
    {
        $user = $request->user();

        // Log the data export action
        ActivityLog::log(
            'data_export',
            "User {$user->name} requested data export",
            $user->id,
            ['email' => $user->email]
        );

        // Gather all user data
        $userData = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'language' => $user->language,
                'preferred_translations' => $user->preferred_translations,
                'appearance_preferences' => $user->appearance_preferences,
                'onboarding_completed' => $user->onboarding_completed,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'created_at' => $user->created_at->toISOString(),
                'updated_at' => $user->updated_at->toISOString(),
            ],
            'roles' => $user->roles->map(fn ($role) => [
                'name' => $role->name,
                'description' => $role->description,
            ]),
            'notes' => $user->hasMany(\App\Models\Note::class)->get()->map(fn ($note) => [
                'id' => $note->id,
                'verse_id' => $note->verse_id,
                'content' => $note->content,
                'created_at' => $note->created_at->toISOString(),
                'updated_at' => $note->updated_at->toISOString(),
            ]),
            'verse_highlights' => $user->hasMany(\App\Models\VerseHighlight::class)->get()->map(fn ($highlight) => [
                'id' => $highlight->id,
                'verse_id' => $highlight->verse_id,
                'color' => $highlight->color,
                'created_at' => $highlight->created_at->toISOString(),
            ]),
            'lessons' => $user->lessons()->get()->map(fn ($lesson) => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'description' => $lesson->description,
                'is_series' => $lesson->is_series,
                'created_at' => $lesson->created_at->toISOString(),
                'updated_at' => $lesson->updated_at->toISOString(),
            ]),
            'lesson_progress' => $user->hasMany(\App\Models\LessonProgress::class)->get()->map(fn ($progress) => [
                'lesson_id' => $progress->lesson_id,
                'completed' => $progress->completed,
                'created_at' => $progress->created_at->toISOString(),
            ]),
            'reading_progress' => $user->hasMany(\App\Models\ReadingProgress::class)->get()->map(fn ($progress) => [
                'chapter_id' => $progress->chapter_id,
                'completed' => $progress->completed,
                'created_at' => $progress->created_at->toISOString(),
            ]),
            'verse_link_canvases' => $user->hasMany(\App\Models\VerseLinkCanvas::class)->get()->map(fn ($canvas) => [
                'id' => $canvas->id,
                'title' => $canvas->title,
                'description' => $canvas->description,
                'created_at' => $canvas->created_at->toISOString(),
                'updated_at' => $canvas->updated_at->toISOString(),
            ]),
            'exported_at' => now()->toISOString(),
        ];

        // Create temporary directory for export
        $exportDir = storage_path('app/exports/'.$user->id);
        if (! file_exists($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        // Save JSON file
        $jsonPath = $exportDir.'/user_data.json';
        file_put_contents($jsonPath, json_encode($userData, JSON_PRETTY_PRINT));

        // Create ZIP file
        $zipPath = storage_path('app/exports/user_'.$user->id.'_data_'.date('Y-m-d_His').'.zip');
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFile($jsonPath, 'user_data.json');

            // Add README
            $readme = "User Data Export\n\n";
            $readme .= "User: {$user->name} ({$user->email})\n";
            $readme .= 'Exported: '.now()->toDateTimeString()."\n\n";
            $readme .= "This archive contains all your personal data from the Bible application.\n";
            $readme .= "The data is provided in JSON format for easy portability.\n";

            $zip->addFromString('README.txt', $readme);
            $zip->close();
        }

        // Clean up JSON file
        unlink($jsonPath);
        rmdir($exportDir);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
