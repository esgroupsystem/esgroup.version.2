<?php

namespace Tests\Unit\Services\Payroll;

use App\Services\Payroll\SssContributionService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SssContributionServiceTest extends TestCase
{
    private SssContributionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SssContributionService::class);
    }

    #[DataProvider('officialContributionRows')]
    public function test_it_matches_sss_circular_2024_006_rows(
        float $compensation,
        float $expectedMsc,
        float $expectedEmployee,
        float $expectedEmployer,
        float $expectedEc,
        float $expectedTotal
    ): void {
        $result = $this->service->compute($compensation);

        $this->assertSame($expectedMsc, $result['msc']);
        $this->assertSame($expectedEmployee, $result['employee']);
        $this->assertSame($expectedEmployer, $result['employer']);
        $this->assertSame($expectedEc, $result['ec']);
        $this->assertSame($expectedTotal, $result['total_contribution']);
        $this->assertSame('2024-006', $result['circular_number']);
        $this->assertSame('2025-01-01', $result['effective_from']);
    }

    public static function officialContributionRows(): array
    {
        return [
            'below first boundary' => [5249.99, 5000.00, 250.00, 500.00, 10.00, 760.00],
            'first middle boundary' => [5250.00, 5500.00, 275.00, 550.00, 10.00, 835.00],
            'last low EC bracket' => [14749.99, 14500.00, 725.00, 1450.00, 10.00, 2185.00],
            'first high EC bracket' => [14750.00, 15000.00, 750.00, 1500.00, 30.00, 2280.00],
            'first MPF bracket' => [20250.00, 20500.00, 1025.00, 2050.00, 30.00, 3105.00],
            'maximum bracket' => [34750.00, 35000.00, 1750.00, 3500.00, 30.00, 5280.00],
        ];
    }

    public function test_every_monthly_salary_credit_bracket_matches_the_official_rates(): void
    {
        for ($msc = 5000; $msc <= 35000; $msc += 500) {
            $compensation = match (true) {
                $msc === 5000 => 5249.99,
                $msc === 35000 => 34750.00,
                default => $msc - 250.00,
            };

            $result = $this->service->compute($compensation);
            $expectedEmployee = round($msc * 0.05, 2);
            $expectedEmployer = round($msc * 0.10, 2);
            $expectedEc = $msc <= 14500 ? 10.00 : 30.00;

            $this->assertSame((float) $msc, $result['msc'], "MSC mismatch for compensation {$compensation}");
            $this->assertSame($expectedEmployee, $result['employee'], "Employee share mismatch for MSC {$msc}");
            $this->assertSame($expectedEmployer, $result['employer'], "Employer share mismatch for MSC {$msc}");
            $this->assertSame($expectedEc, $result['ec'], "EC mismatch for MSC {$msc}");
            $this->assertSame(
                round($expectedEmployee + $expectedEmployer + $expectedEc, 2),
                $result['total_contribution'],
                "Total contribution mismatch for MSC {$msc}"
            );
        }
    }

    public function test_it_splits_regular_ss_and_mpf_at_twenty_thousand_msc(): void
    {
        $result = $this->service->compute(20250.00);

        $this->assertSame(20000.00, $result['regular_ss_msc']);
        $this->assertSame(500.00, $result['mpf_msc']);
        $this->assertSame(1000.00, $result['employee_regular_ss']);
        $this->assertSame(25.00, $result['employee_mpf']);
        $this->assertSame(2000.00, $result['employer_regular_ss']);
        $this->assertSame(50.00, $result['employer_mpf']);
    }
}
