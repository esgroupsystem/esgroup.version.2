<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Biometrics;

use App\Models\MirasolBiometricsLog;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/** Punches (`mirasol_biometrics_logs`) from CrossChex devices and from manual WFH encoding. */
interface BiometricsLogRepositoryInterface
{
    /**
     * People seen in the logs (one per employee number), optionally matching a search on name,
     * employee no / id, source id or CrossChex id.
     *
     * @return Collection<int, array{employee_no: ?string, employee_name: ?string, biometric_employee_id: ?string}>
     */
    public function people(?string $search = null): Collection;

    /**
     * Punches between the two dates (whole days) of the given employee numbers or biometric ids,
     * by name then time.
     *
     * @param  Collection<int, string>  $employeeNos
     * @param  Collection<int, string>  $biometricIds
     * @return Collection<int, MirasolBiometricsLog>
     */
    public function forPeople(Collection $employeeNos, Collection $biometricIds, CarbonInterface $from, CarbonInterface $to): Collection;

    /**
     * One employee's punches in one CrossChex account from $from to $to (inclusive datetimes), by time.
     *
     * @return Collection<int, MirasolBiometricsLog>
     */
    public function forIdentity(string $employeeNo, string $account, CarbonInterface $from, CarbonInterface $to, ?string $deviceSn = null): Collection;

    /**
     * Manual punches of a work date, including an overnight Check Out saved on the next day.
     *
     * @return Collection<int, MirasolBiometricsLog>
     */
    public function manualForDate(string $employeeNo, string $account, string $deviceSn, CarbonInterface $workDate): Collection;

    /** @param list<int> $ids */
    public function deleteMany(array $ids): void;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): MirasolBiometricsLog;
}
