<?php

namespace App\Mail;

use App\Models\JobOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JobOrderCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $job;

    public function __construct(JobOrder $job)
    {
        $job->loadMissing('bus');
        $this->job = $job;
    }

    public function build()
    {
        return $this->subject("New Job Order #{$this->job->id}")
            ->view('emails.job_order_created')
            ->with([
                'job' => $this->job
            ]);
    }
}
