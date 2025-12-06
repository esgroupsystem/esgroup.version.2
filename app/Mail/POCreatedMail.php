<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class POCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $po;

    public function __construct(PurchaseOrder $po)
    {
        $this->po = $po->loadMissing('items.product.category', 'requester');
    }

    public function build()
    {
        return $this->subject("New Purchase Order: {$this->po->po_number}")
            ->view('emails.po_created')
            ->with([
                'po' => $this->po
            ]);
    }
}
