<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\CctvConcernStatus;
use App\Enums\ItTicketApprovalStatus;
use App\Enums\ItTicketStatus;
use PHPUnit\Framework\TestCase;

final class ItModuleStatusTest extends TestCase
{
    public function test_cctv_status_values_match_existing_contract(): void
    {
        self::assertSame(
            ['Open', 'In Progress', 'Fixed', 'Closed'],
            CctvConcernStatus::values(),
        );
        self::assertTrue(CctvConcernStatus::Fixed->isCompleted());
        self::assertFalse(CctvConcernStatus::InProgress->isCompleted());
    }

    public function test_it_ticket_status_transition_helpers_are_explicit(): void
    {
        self::assertFalse(ItTicketStatus::Completed->canBeDeleted());
        self::assertFalse(ItTicketStatus::InProgress->canBeDeleted());
        self::assertTrue(ItTicketStatus::Pending->canBeDeleted());
        self::assertSame('Approved', ItTicketApprovalStatus::Approved->value);
    }
}
