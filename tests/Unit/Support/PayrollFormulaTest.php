<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Payroll\PayrollFormula;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PayrollFormulaTest extends TestCase
{
    private const VALUES = [
        'basic_pay' => 10000,
        'days_absent' => 0,
        'minutes_late' => 45,
        'days_worked' => 0,
        'cutoff' => 2,
    ];

    /** @return array<string, array{string, float}> */
    public static function formulas(): array
    {
        return [
            'percent sign' => ['basic_pay * 5%', 500.0],
            'percent first' => ['5% * basic_pay', 500.0],
            'single equals and AND' => ['IF(days_absent = 0 AND minutes_late = 0, 1000, 0)', 0.0],
            'lower case names' => ['if(cutoff == 2, 500, 0)', 500.0],
            'division by zero is zero' => ['basic_pay / days_worked', 0.0],
            'IF skips the other branch' => ['IF(days_worked > 0, basic_pay / days_worked, 7)', 7.0],
            'min max' => ['MIN(MAX(minutes_late - 30, 0) * 5, 50)', 50.0],
            'round' => ['ROUND(10 / 3, 2)', 3.33],
            'precedence' => ['2 + 3 * 4 ^ 2', 50.0],
            'power is right associative' => ['2 ^ 3 ^ 2', 512.0],
            'unary minus' => ['-2 ^ 2', -4.0],
            'not and or' => ['NOT days_absent OR 0', 1.0],
            'symbols' => ['minutes_late >= 45 && cutoff <> 1', 1.0],
            'floor ceil abs' => ['FLOOR(2.7) + CEIL(2.1) + ABS(-1)', 6.0],
        ];
    }

    #[DataProvider('formulas')]
    public function test_it_computes(string $formula, float $expected): void
    {
        $this->assertEqualsWithDelta($expected, PayrollFormula::evaluate($formula, self::VALUES), 0.000001);
    }

    /** @return array<string, array{string, string}> */
    public static function invalid(): array
    {
        return [
            'empty' => ['', 'empty'],
            'unknown value' => ['salary * 2', 'Unknown value: salary'],
            'unknown function' => ['SUM(1, 2)', 'Unknown function "SUM"'],
            'wrong argument count' => ['IF(1, 2)', 'IF needs 3'],
            'bad character' => ['basic_pay $ 2', 'not allowed'],
            'missing bracket' => ['(1 + 2', 'Missing ")"'],
            'ends early' => ['1 +', 'ends too early'],
            'two numbers' => ['1 2', 'Unexpected "2"'],
            'no code' => ['system(1)', 'Unknown function "SYSTEM"'],
            'no text' => ['system("x")', 'is not allowed'],
        ];
    }

    #[DataProvider('invalid')]
    public function test_it_rejects_bad_formulas_with_a_readable_message(string $formula, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        PayrollFormula::evaluate($formula, self::VALUES);
    }

    public function test_it_lists_the_names_a_formula_uses(): void
    {
        $this->assertSame(['days_absent', 'basic_pay'], PayrollFormula::referencedNames('IF(DAYS_ABSENT = 0, basic_pay * 2%, 0)'));
    }
}
