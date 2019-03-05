<?php

namespace Logistics\Notifications\Tenant;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Logistics\DB\Tenant\Invoice;
use Illuminate\Support\Facades\Mail;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvoiceActivity extends Notification implements ShouldQueue
{
    use Queueable;

    public $invoice;
    public $tenantLang;
    public $userFullname;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice, $tenantLang, $userFullname)
    {
        $this->invoice = $invoice;
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
        $client = $this->invoice->client;
        $branch = $client->branch;
        $box = $branch->code .''.$client->manual_id_dsp;

        return [
            'title' => $this->userFullname . ' ' . __('created an invoice'),
            'content' => __('The invoice #:id has been created for: :box', ['id' => $this->invoice->manual_id_dsp, 'box' => $box, ]),
            'created_at' => $this->invoice->created_at,
        ];
    }
}
