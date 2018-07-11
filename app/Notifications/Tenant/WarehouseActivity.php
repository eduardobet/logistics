<?php

namespace Logistics\Notifications\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WarehouseActivity extends Notification implements ShouldQueue
{
    use Queueable;

    public $createdAtAgo;
    public $warehouseId;
    public $box;
    public $invoiceId;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(String $createdAtAgo, int $warehouseId, String $box, int $invoiceId)
    {
        $this->createdAtAgo = $createdAtAgo;
        $this->warehouseId = $warehouseId;
        $this->box = $box;
        $this->invoiceId = $invoiceId;
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
        return [
            'title' => auth()->user()->full_name . ' ' . __('created a warehouse'),
            'content' => __('The warehouse #:id has been created for: :box with invoice id #:iid', ['id' => $this->warehouseId, 'box' => $this->box, 'iid' => $this->invoiceId, ]),
            'created_at' => $this->createdAtAgo,
        ];
    }
}
