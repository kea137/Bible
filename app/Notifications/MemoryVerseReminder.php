<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemoryVerseReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected int $dueCount;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $dueCount)
    {
        $this->dueCount = $dueCount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verseText = $this->dueCount === 1 ? 'verse' : 'verses';
        
        return (new MailMessage)
            ->subject('Memory Verse Review Reminder')
            ->line("You have {$this->dueCount} memory {$verseText} due for review today.")
            ->line('Regular review helps strengthen your memorization and improves retention.')
            ->action('Review Now', url('/mobile/memory-verses/due'))
            ->line('Keep up the great work with your Bible memorization!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'memory_verse_reminder',
            'due_count' => $this->dueCount,
            'message' => "You have {$this->dueCount} memory verse(s) due for review today.",
        ];
    }
}
