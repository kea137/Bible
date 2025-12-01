<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Models\Note;
use App\Models\Verse;
use Illuminate\Console\Command;

class ReindexSearchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:reindex {model?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex all searchable models for Scout/Meilisearch. Optionally specify a model: verses, notes, or lessons';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model = $this->argument('model');

        if ($model && ! in_array($model, ['verses', 'notes', 'lessons'])) {
            $this->error('Invalid model. Choose from: verses, notes, lessons');

            return 1;
        }

        $this->info('Starting search index rebuild...');

        if (! $model || $model === 'verses') {
            $this->indexModel(Verse::class, 'Verses');
        }

        if (! $model || $model === 'notes') {
            $this->indexModel(Note::class, 'Notes');
        }

        if (! $model || $model === 'lessons') {
            $this->indexModel(Lesson::class, 'Lessons');
        }

        $this->info('Search indexing completed successfully!');

        return 0;
    }

    /**
     * Index a specific model
     */
    private function indexModel(string $modelClass, string $label)
    {
        $this->info("Indexing {$label}...");

        $count = $modelClass::count();
        $bar = $this->output->createProgressBar($count);

        $modelClass::chunk(500, function ($models) use ($bar) {
            $models->searchable();
            $bar->advance($models->count());
        });

        $bar->finish();
        $this->newLine();
        $this->info("{$label} indexed: {$count} records");
    }
}
