<?php

namespace App\Console\Commands;

use App\Models\MemoryVerse;
use App\Models\User;
use App\Notifications\MemoryVerseReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendMemoryVerseReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memory-verses:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to users with memory verses due for review';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for users with due memory verses...');

        // Get all users who have memory verses due today
        $usersWithDueVerses = User::whereHas('memoryVerses', function ($query) {
            $query->where('next_review_date', '<=', Carbon::now()->toDateString());
        })->get();

        $notificationsSent = 0;

        foreach ($usersWithDueVerses as $user) {
            $dueCount = $user->memoryVerses()
                ->where('next_review_date', '<=', Carbon::now()->toDateString())
                ->count();

            if ($dueCount > 0) {
                $user->notify(new MemoryVerseReminder($dueCount));
                $notificationsSent++;
                $this->info("Sent reminder to {$user->email} for {$dueCount} verse(s)");
            }
        }

        $this->info("Sent {$notificationsSent} reminder(s) successfully.");

        return Command::SUCCESS;
    }
}
