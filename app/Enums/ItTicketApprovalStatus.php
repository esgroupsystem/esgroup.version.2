<?php

declare(strict_types=1);

namespace App\Enums;

enum ItTicketApprovalStatus: string
{
    case Approval = 'Approval';
    case Approved = 'Approved';
    case Disapproved = 'Disapproved';
}
