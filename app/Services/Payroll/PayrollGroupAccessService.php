<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\EmployeeBiometric;

/**
 * Which payroll groups the signed-in user may see and act on. The `payroll.group` middleware
 * stores "all" or a list of group numbers in the session; with no value the user sees nothing.
 */
final class PayrollGroupAccessService
{
    /** @return string|list<int|string>|null "all", the allowed groups, or null */
    public function allowed(): string|array|null
    {
        return session('payroll_allowed_groups');
    }

    public function allows(int|string|null $group): bool
    {
        $allowed = $this->allowed();
        if ($allowed === 'all') {
            return true;
        }

        return in_array((int) $group, array_map('intval', (array) ($allowed ?? [])), true);
    }

    /** @return array<string, string> group => label, only the allowed groups */
    public function options(): array
    {
        return collect(EmployeeBiometric::GROUP_LABELS)
            ->filter(fn (string $label, int $group): bool => $this->allows($group))
            ->mapWithKeys(fn (string $label, int $group): array => [(string) $group => $label])
            ->all();
    }
}
