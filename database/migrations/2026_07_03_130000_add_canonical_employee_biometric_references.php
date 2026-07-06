<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $canonicalReferenceTables = [
        'daily_attendance_summaries',
        'payroll_attendance_adjustments',
        'employee_plotting_schedules',
        'payroll_employee_salaries',
        'payroll_items',
        'payroll_report_logs',
    ];

    public function up(): void
    {
        $this->upgradeEmployeeBiometricsTable();

        foreach ($this->canonicalReferenceTables as $table) {
            $this->addEmployeeBiometricReference($table);
        }

        $this->backfillEmployeeIdentityHashes();
        $this->backfillCanonicalReferences();
    }

    public function down(): void
    {
        foreach (array_reverse($this->canonicalReferenceTables) as $table) {
            $this->dropEmployeeBiometricReference($table);
        }

        if (Schema::hasTable('employee_biometrics')) {
            Schema::table('employee_biometrics', function (Blueprint $table): void {
                $this->dropIndexIfExists($table, 'employee_biometrics_identity_hash_unique');
                $this->dropIndexIfExists($table, 'employee_biometrics_payroll_active_status_index');
                $this->dropIndexIfExists($table, 'employee_biometrics_group_status_index');

                foreach ([
                    'employee_identity_hash',
                    'group_name',
                    'is_payroll_active',
                    'inactive_at',
                ] as $column) {
                    if (Schema::hasColumn('employee_biometrics', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function upgradeEmployeeBiometricsTable(): void
    {
        if (! Schema::hasTable('employee_biometrics')) {
            return;
        }

        Schema::table('employee_biometrics', function (Blueprint $table): void {
            if (! Schema::hasColumn('employee_biometrics', 'employee_identity_hash')) {
                $table->string('employee_identity_hash', 64)
                    ->nullable()
                    ->after('source_key');
            }

            if (! Schema::hasColumn('employee_biometrics', 'group_name')) {
                $table->string('group_name')
                    ->nullable()
                    ->after('employment_status');
            }

            if (! Schema::hasColumn('employee_biometrics', 'is_payroll_active')) {
                $table->boolean('is_payroll_active')
                    ->default(true)
                    ->after('group_name');
            }

            if (! Schema::hasColumn('employee_biometrics', 'inactive_at')) {
                $table->timestamp('inactive_at')
                    ->nullable()
                    ->after('is_payroll_active');
            }
        });

        Schema::table('employee_biometrics', function (Blueprint $table): void {
            $this->addIndexIfMissing($table, 'employee_biometrics_payroll_active_status_index', [
                'is_payroll_active',
                'employment_status',
            ]);

            $this->addIndexIfMissing($table, 'employee_biometrics_group_status_index', [
                'group_name',
                'employment_status',
            ]);

            $this->addUniqueIfMissing($table, 'employee_biometrics_identity_hash_unique', [
                'employee_identity_hash',
            ]);
        });
    }

    private function addEmployeeBiometricReference(string $tableName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (! Schema::hasColumn($tableName, 'employee_biometric_id')) {
                $table->foreignId('employee_biometric_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('employee_biometrics')
                    ->nullOnDelete();
            }
        });

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            $this->addIndexIfMissing($table, $this->employeeBiometricIndexName($tableName), [
                'employee_biometric_id',
            ]);

            if (Schema::hasColumn($tableName, 'work_date')) {
                $this->addIndexIfMissing($table, $this->employeeBiometricWorkDateIndexName($tableName), [
                    'employee_biometric_id',
                    'work_date',
                ]);
            }
        });
    }

    private function dropEmployeeBiometricReference(string $tableName): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'employee_biometric_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            $this->dropIndexIfExists($table, $this->employeeBiometricWorkDateIndexName($tableName));
            $this->dropIndexIfExists($table, $this->employeeBiometricIndexName($tableName));

            try {
                $table->dropConstrainedForeignId('employee_biometric_id');
            } catch (Throwable) {
                try {
                    $table->dropForeign(['employee_biometric_id']);
                } catch (Throwable) {
                    // Foreign key name may differ on older installations.
                }

                $table->dropColumn('employee_biometric_id');
            }
        });
    }

    private function backfillEmployeeIdentityHashes(): void
    {
        if (
            ! Schema::hasTable('employee_biometrics')
            || ! Schema::hasColumn('employee_biometrics', 'employee_identity_hash')
        ) {
            return;
        }

        DB::table('employee_biometrics')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    $hash = $this->makeEmployeeIdentityHash($row);

                    if ($hash === null) {
                        continue;
                    }

                    $duplicateExists = DB::table('employee_biometrics')
                        ->where('employee_identity_hash', $hash)
                        ->where('id', '!=', $row->id)
                        ->exists();

                    if ($duplicateExists) {
                        continue;
                    }

                    DB::table('employee_biometrics')
                        ->where('id', $row->id)
                        ->update([
                            'employee_identity_hash' => $hash,
                            'is_payroll_active' => $this->truthy($row->is_payroll_active ?? true),
                            'employment_status' => $this->clean($row->employment_status ?? null) ?: 'active',
                        ]);
                }
            });
    }

    private function backfillCanonicalReferences(): void
    {
        foreach ($this->canonicalReferenceTables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'employee_biometric_id')) {
                continue;
            }

            DB::table($tableName)
                ->whereNull('employee_biometric_id')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($tableName): void {
                    foreach ($rows as $row) {
                        $employeeBiometricId = $this->resolveEmployeeBiometricId($row);

                        if ($employeeBiometricId === null) {
                            continue;
                        }

                        DB::table($tableName)
                            ->where('id', $row->id)
                            ->update([
                                'employee_biometric_id' => $employeeBiometricId,
                            ]);
                    }
                });
        }
    }

    private function resolveEmployeeBiometricId(object $row): ?int
    {
        $legacyBiometric = $this->clean($row->biometric_employee_id ?? null);
        $employeeNo = $this->clean($row->employee_no ?? null);
        $employeeName = $this->clean($row->employee_name ?? null);
        $crosschexId = $this->clean($row->crosschex_id ?? null);

        $identifierValues = collect([
            $legacyBiometric,
            $employeeNo,
            $crosschexId,
        ])->filter()->unique()->values();

        if ($identifierValues->isNotEmpty()) {
            $match = DB::table('employee_biometrics')
                ->where(function ($query) use ($identifierValues): void {
                    foreach ($identifierValues as $value) {
                        $query
                            ->orWhere('source_employee_id', $value)
                            ->orWhere('source_employee_no', $value)
                            ->orWhere('display_employee_no', $value)
                            ->orWhere('source_crosschex_id', $value)
                            ->orWhere('source_crosschex_account', $value)
                            ->orWhere('source_key', $value);
                    }
                })
                ->orderByDesc('is_payroll_active')
                ->orderByRaw("CASE WHEN employment_status = 'active' THEN 1 ELSE 0 END DESC")
                ->orderBy('id')
                ->first();

            if ($match) {
                return (int) $match->id;
            }
        }

        if ($employeeName !== null) {
            $normalizedName = mb_strtolower($employeeName);

            $match = DB::table('employee_biometrics')
                ->where(function ($query) use ($normalizedName): void {
                    $query
                        ->whereRaw('LOWER(TRIM(display_name)) = ?', [$normalizedName])
                        ->orWhereRaw('LOWER(TRIM(source_employee_name)) = ?', [$normalizedName])
                        ->orWhereRaw('LOWER(TRIM(source_crosschex_account_name)) = ?', [$normalizedName]);
                })
                ->orderByDesc('is_payroll_active')
                ->orderByRaw("CASE WHEN employment_status = 'active' THEN 1 ELSE 0 END DESC")
                ->orderBy('id')
                ->first();

            if ($match) {
                return (int) $match->id;
            }
        }

        if ($legacyBiometric !== null && ctype_digit($legacyBiometric)) {
            $match = DB::table('employee_biometrics')
                ->where('id', (int) $legacyBiometric)
                ->first();

            if ($match) {
                return (int) $match->id;
            }
        }

        return null;
    }

    private function makeEmployeeIdentityHash(object $row): ?string
    {
        $companyId = $this->clean($row->biometric_company_id ?? null) ?: 'global';

        foreach ([
            'source_employee_id',
            'source_employee_no',
            'display_employee_no',
            'source_crosschex_id',
            'source_crosschex_account',
            'source_employee_name',
            'display_name',
        ] as $field) {
            $value = $this->clean($row->{$field} ?? null);

            if ($value !== null) {
                return hash('sha256', $companyId.'|'.$field.'|'.mb_strtolower($value));
            }
        }

        return null;
    }

    private function employeeBiometricIndexName(string $tableName): string
    {
        return match ($tableName) {
            'daily_attendance_summaries' => 'das_ebi_idx',
            'payroll_attendance_adjustments' => 'paa_ebi_idx',
            'employee_plotting_schedules' => 'eps_ebi_idx',
            'payroll_employee_salaries' => 'pes_ebi_idx',
            'payroll_items' => 'pi_ebi_idx',
            'payroll_report_logs' => 'prl_ebi_idx',
            default => substr($tableName, 0, 24).'_ebi_idx',
        };
    }

    private function employeeBiometricWorkDateIndexName(string $tableName): string
    {
        return match ($tableName) {
            'daily_attendance_summaries' => 'das_ebi_work_idx',
            'payroll_attendance_adjustments' => 'paa_ebi_work_idx',
            'employee_plotting_schedules' => 'eps_ebi_work_idx',
            'payroll_employee_salaries' => 'pes_ebi_work_idx',
            'payroll_items' => 'pi_ebi_work_idx',
            'payroll_report_logs' => 'prl_ebi_work_idx',
            default => substr($tableName, 0, 20).'_ebi_work_idx',
        };
    }

    private function addIndexIfMissing(Blueprint $table, string $indexName, array $columns): void
    {
        if (! $this->indexExists($table->getTable(), $indexName)) {
            $table->index($columns, $indexName);
        }
    }

    private function addUniqueIfMissing(Blueprint $table, string $indexName, array $columns): void
    {
        if (! $this->indexExists($table->getTable(), $indexName)) {
            $table->unique($columns, $indexName);
        }
    }

    private function dropIndexIfExists(Blueprint $table, string $indexName): void
    {
        if ($this->indexExists($table->getTable(), $indexName)) {
            $table->dropIndex($indexName);
        }
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }

    private function clean(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array((string) $value, ['1', 'true', 'yes', 'active'], true);
    }
};
