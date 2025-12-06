<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class POCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public PurchaseOrder $po)
    {
        $this->po->loadMissing('items.product', 'requester');
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title'   => 'New Purchase Order Created',
            'message' => "PO {$this->po->po_number} has been created by {$this->po->requester->full_name} for garage {$this->po->garage}.",
            'po_id'   => $this->po->id,
            'po_number' => $this->po->po_number,
            'status'  => $this->po->status,
        ];
    }
}
