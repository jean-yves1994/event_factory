<?php

namespace App\Mail;

use App\Models\Requisition;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequisitionApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Requisition $requisition;

    public function __construct(Requisition $requisition)
    {
        $this->requisition = $requisition;
    }

    public function build()
    {
        return $this->subject('New Approved Requisition')
            ->view('emails.requisition-approved');
    }
}