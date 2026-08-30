<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\PayrollEmployeeNameFormatter;
use PHPUnit\Framework\TestCase;

class PayrollEmployeeNameFormatterTest extends TestCase
{
    public function test_it_displays_payroll_names_surname_first(): void
    {
        $this->assertSame(
            'Ilaw, Lenberd Arazo',
            PayrollEmployeeNameFormatter::display('Lenberd Arazo Ilaw')
        );
    }

    public function test_it_preserves_names_already_stored_surname_first(): void
    {
        $this->assertSame(
            'Ilaw, Lenberd Arazo',
            PayrollEmployeeNameFormatter::display('Ilaw, Lenberd Arazo')
        );
    }
}
