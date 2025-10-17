<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [10, 30, 60];

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $title,
        public string $message,
        public string $type = 'info', // info, success, warning, error
        public ?string $actionUrl = null,
        public ?string $actionText = null,
        public bool $sendEmail = false, // Control si envía email
        public bool $broadcast = true, // Control si envía broadcast (tiempo real)
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->sendEmail) {
            $channels[] = 'mail';
        }

        if ($this->broadcast) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    /**
     * Determine which connections should be used for each notification channel.
     *
     * @return array<string, string>
     */
    public function viaConnections(): array
    {
        return [
            'mail' => 'database',     // Email por cola database
            'database' => 'sync',     // Database síncrono (inmediato)
            'broadcast' => 'sync',    // Broadcast inmediato (tiempo real)
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject($this->title)
            ->greeting($this->getGreeting())
            ->line($this->message);

        // Agregar botón de acción si existe
        if ($this->actionUrl && $this->actionText) {
            $mailMessage->action($this->actionText, url($this->actionUrl));
        }

        // Agregar línea final según el tipo
        $mailMessage->line($this->getClosingLine());

        // Personalizar nivel según tipo
        return match ($this->type) {
            'success' => $mailMessage->success(),
            'error' => $mailMessage->error(),
            default => $mailMessage,
        };
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
        ];
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
            'action_text' => $this->actionText,
        ];
    }

    /**
     * Get greeting based on notification type.
     */
    protected function getGreeting(): string
    {
        return match ($this->type) {
            'success' => '¡Excelente!',
            'error' => 'Atención',
            'warning' => 'Aviso Importante',
            default => '¡Hola!',
        };
    }

    /**
     * Get closing line based on notification type.
     */
    protected function getClosingLine(): string
    {
        return match ($this->type) {
            'error' => 'Si necesitas ayuda, no dudes en contactarnos.',
            'warning' => 'Por favor, toma acción lo antes posible.',
            default => 'Gracias por usar nuestra aplicación.',
        };
    }
}
