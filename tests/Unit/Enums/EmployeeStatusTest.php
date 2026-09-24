<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\EmployeeStatus;
use PHPUnit\Framework\TestCase;

final class EmployeeStatusTest extends TestCase
{
    public function test_values_match_existing_employee_status_contract(): void
    {
        $this->assertSame([
            'Active',
            'Active(Re-Entry)',
            // Written by LeaveRecordService; must be accepted by the profile form.
            'On Leave',
            'Inactive',
            'Suspended',
            'Terminated',
            'Terminated(due to AWOL)',
            'End of Contract',
            'Retrench',
            'Retired',
            'Resigned',
        ], EmployeeStatus::values());
    }

    public function test_active_and_inactive_groups_are_explicit(): void
    {
        $this->assertSame(['Active', 'Active(Re-Entry)'], EmployeeStatus::activeValues());
        $this->assertContains('Terminated(due to AWOL)', EmployeeStatus::inactiveValues());
        $this->assertNotContains('Suspended', EmployeeStatus::inactiveValues());
        // On leave is still employed: neither in the active count nor the inactive one.
        $this->assertNotContains('On Leave', EmployeeStatus::activeValues());
        $this->assertNotContains('On Leave', EmployeeStatus::inactiveValues());
    }
}
