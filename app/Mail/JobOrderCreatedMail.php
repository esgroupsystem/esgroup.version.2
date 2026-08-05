<?php

namespace App\Mail;

use App\Models\JobOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JobOrderCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public JobOrder $job
    ) {
        $this->job->loadMissing('bus');

        $this->afterCommit();
        $this->onQueue('emails');
    }

    public function backoff(): array
    {
        return [
            60,
            300,
            900,
        ];
    }

    public function build(): self
    {
        return $this
            ->subject("New Job Order #{$this->job->id}")
            ->view('emails.job_order_created')
            ->with([
                'job' => $this->job,
            ]);
    }
}
