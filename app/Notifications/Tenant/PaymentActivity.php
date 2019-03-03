<?php

namespace Logistics\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Payment;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentActivity extends Notification implements ShouldQueue
{
    use Queueable;

    public $payment;
    public $clientId;
    public $tenantLang;
    public $userFullname;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Payment $payment, $clientId, $tenantLang, $userFullname)
    {
        $this->payment = $payment;
        $this->clientId = $clientId;
        $this->tenantLang = $tenantLang;
        $this->userFullname = $userFullname;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $client = Client::find($this->clientId);
        $branch = $client->branch;
        $box = $branch->code . '' . $client->manual_id_dsp;
        $lang = $this->tenantLang ? : localization()->getCurrentLocale();

        return [
            'title' => $this->userFullname . ' ' . __('created a payment', [], $lang),
            'content' => __('The payment #:pid has been created to the invoice #iid: (:box)', [
                'pid' => $this->payment->id, 'iid' => $this->payment->invoice_id, 'box' => $box,
            ], $lang),
            'created_at' => $this->payment->created_at,
        ];
    }
}
