<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\BenefitProgram;
use App\Enums\HrPositionType;
use App\Enums\LeaveActionType;
use App\Enums\LeaveStatus;
use PHPUnit\Framework\TestCase;

final class HrModuleEnumTest extends TestCase
{
    public function test_leave_status_contract_is_explicit(): void
    {
        self::assertSame('Active', LeaveStatus::Active->value);
        self::assertTrue(LeaveStatus::Cancelled->isClosed());
        self::assertTrue(LeaveStatus::Completed->isClosed());
        self::assertFalse(LeaveStatus::Inactive->isClosed());
    }

    public function test_leave_actions_define_proof_requirements(): void
    {
        self::assertTrue(LeaveActionType::FirstNotice->requiresProof());
        self::assertTrue(LeaveActionType::Terminate->requiresProof());
        self::assertFalse(LeaveActionType::Cancel->requiresProof());
    }

    public function test_hr_position_and_benefit_program_values_preserve_existing_contracts(): void
    {
        self::assertSame('Driver', HrPositionType::Driver->value);
        self::assertSame('Conductor', HrPositionType::Conductor->value);
        self::assertSame('SSS', BenefitProgram::Sss->label());
        self::assertSame('philhealth', BenefitProgram::PhilHealth->value);
        self::assertSame('Pag-IBIG', BenefitProgram::PagIbig->label());
    }
}
