<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class HealthcareNotification extends Notification
{

    public $title;
    public $message;
    public $type;
    public $actionUrl;
    public $data;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $message, $type = 'info', $actionUrl = null, $data = [])
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type; // info, success, warning, error
        $this->actionUrl = $actionUrl;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'action_url' => $this->actionUrl,
            'data' => $this->data,
            'created_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'action_url' => $this->actionUrl,
            'data' => $this->data,
        ];
    }

    /**
     * Get the broadcastable type of the notification.
     */
    public function broadcastType(): string
    {
        return 'healthcare.notification';
    }
}
