<?php

namespace Logistics\Notifications\Tenant;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Logistics\DB\Tenant\Invoice;
use Logistics\DB\Tenant\Payment;
use Illuminate\Support\Facades\Mail;
use Logistics\Mail\Tenant\InvoiceCreated;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Notifications\Messages\MailMessage;

class InvoiceActivity extends Notification implements ShouldQueue
{
    use Queueable;

    public $invoice;
    public $payment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice, Payment $payment)
    {
        $this->invoice = $invoice;
        $this->payment = $payment;
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
        $box = $client->boxes()->active()->first();
        $box = $box->branch_code .''.$client->id;

        Mail::to($client)->send(new InvoiceCreated($this->invoice, $this->payment));

        return [
            'title' => auth()->user()->full_name . ' ' . __('created an invoice'),
            'content' => __('The invoice #:id has been created for: :box with payment id #:pid', ['id' => $this->invoice->id, 'box' => $box, 'pid' => $this->payment->id, ]),
            'created_at' => $this->invoice->created_at,
        ];
    }
}
