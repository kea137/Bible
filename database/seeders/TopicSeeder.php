<?php

namespace Database\Seeders;

use App\Models\Topic;
use App\Models\Verse;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create topics with sample data
        $topics = [
            [
                'title' => 'Love and Compassion',
                'description' => 'Verses about God\'s love and showing compassion to others',
                'category' => 'Faith & Character',
                'order' => 1,
            ],
            [
                'title' => 'Faith and Trust',
                'description' => 'Building faith and trusting in God',
                'category' => 'Faith & Character',
                'order' => 2,
            ],
            [
                'title' => 'Prayer and Worship',
                'description' => 'The importance of prayer and worship in our lives',
                'category' => 'Spiritual Practices',
                'order' => 1,
            ],
            [
                'title' => 'Wisdom and Understanding',
                'description' => 'Seeking wisdom and understanding from God',
                'category' => 'Growth & Learning',
                'order' => 1,
            ],
            [
                'title' => 'Hope and Encouragement',
                'description' => 'Finding hope and encouragement in difficult times',
                'category' => 'Comfort & Peace',
                'order' => 1,
            ],
        ];

        foreach ($topics as $topicData) {
            Topic::create($topicData);
        }

        // You can add specific verses to topics here if verses exist in the database
        // Example:
        // $loveTopic = Topic::where('title', 'Love and Compassion')->first();
        // if ($loveTopic) {
        //     $johnVerse = Verse::where('verse_number', 16)->whereHas('chapter', function($q) {
        //         $q->where('chapter_number', 3)->whereHas('book', function($b) {
        //             $b->where('title', 'John');
        //         });
        //     })->first();
        //
        //     if ($johnVerse) {
        //         $loveTopic->verses()->attach($johnVerse->id, [
        //             'order' => 1,
        //             'note' => 'The most famous verse about God\'s love',
        //         ]);
        //     }
        // }
    }
}
