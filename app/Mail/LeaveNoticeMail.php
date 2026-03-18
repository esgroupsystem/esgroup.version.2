<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $leave;

    public $noticeType;

    public $category;

    public function __construct($leave, string $noticeType, string $category)
    {
        $this->leave = $leave;
        $this->noticeType = $noticeType;
        $this->category = $category;
    }

    public function build()
    {
        return $this->subject(
            'Leave Notification - '.$this->noticeType.' for '.$this->leave->employee->full_name
        )->view('emails.leave_notice');
    }
}
