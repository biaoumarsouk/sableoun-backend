<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Le token de réinitialisation de mot de passe.
     *
     * @var string
     */
    public $token;

    /**
     * L'URL de votre frontend où l'utilisateur réinitialisera son mot de passe.
     *
     * @var string
     */
    public static $frontUrl;

    public function __construct($token)
    {
        $this->token = $token;
        // On récupère l'URL du frontend depuis le fichier .env
        self::$frontUrl = config('app.frontend_url', url('/'));
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // On construit l'URL complète avec le token et l'email
        $resetUrl = self::$frontUrl . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject(Lang::get('Réinitialisation de votre mot de passe - Sabléoun'))
            ->greeting(Lang::get('Bonjour !'))
            ->line(Lang::get('Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.'))
            ->action(Lang::get('Réinitialiser le mot de passe'), $resetUrl)
            ->line(Lang::get('Ce lien de réinitialisation expirera dans :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('Si vous n\'avez pas demandé de réinitialisation de mot de passe, aucune action supplémentaire n\'est requise.'));
    }
}