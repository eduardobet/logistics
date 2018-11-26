<?php

namespace Logistics\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPwdNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $type = $notifiable->type;
        $tenant = $notifiable->company;
        $lang = $tenant->lang ? $tenant->lang : localization()->getCurrentLocale();

        // URL::forceRootUrl($tenant->domain);

        $url = route(
            'tenant.user.password.reset',
            [$tenant->domain, 'token' => $this->token, 'e' => $notifiable->email,]
        );

        return (new MailMessage)
            ->from($tenant->mail_from_address, $tenant->mail_from_name)
            ->subject(__('Password reset request', [], $lang))
            ->line(__('You are receiving this email because we received a password reset request for your account.', [], $lang))
            ->line(__('The link expires in :time', ['time' => '1 ' . __('Hour') ]))
            ->action(__('Reset Password', [], $lang), $url)
            ->line(__('If you did not request a password reset, no further action is required.', [], $lang))
            ->salutation(__('Regards', [], $lang) . '<br>' . config('app.name'))
            ->greeting(__('Hello', [], $lang));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
