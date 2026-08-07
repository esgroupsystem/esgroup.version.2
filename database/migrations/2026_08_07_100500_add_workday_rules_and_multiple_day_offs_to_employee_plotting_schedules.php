<?php

use App\Enums\WorkdayType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_plotting_schedules')) {
            return;
        }

        $this->expandLegacyColumns();

        Schema::table('employee_plotting_schedules', function (Blueprint $table): void {
            if (! Schema::hasColumn('employee_plotting_schedules', 'workday_type')) {
                $table->string('workday_type', 20)
                    ->default(WorkdayType::EightHours->value)
                    ->after('shift_name');
            }

            if (! Schema::hasColumn('employee_plotting_schedules', 'paid_work_minutes')) {
                $table->unsignedSmallInteger('paid_work_minutes')
                    ->default(WorkdayType::EightHours->paidMinutes())
                    ->after('workday_type');
            }

            if (! Schema::hasColumn('employee_plotting_schedules', 'lunch_break_minutes')) {
                $table->unsignedSmallInteger('lunch_break_minutes')
                    ->default(60)
                    ->after('paid_work_minutes');
            }

            if (! Schema::hasColumn('employee_plotting_schedules', 'day_offs')) {
                $table->json('day_offs')
                    ->nullable()
                    ->after('day_off');
            }
        });

        DB::table('employee_plotting_schedules')
            ->select(['id', 'time_in', 'time_out', 'day_off'])
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    $clockMinutes = $this->clockMinutes($row->time_in, $row->time_out);
                    $workdayType = $clockMinutes >= WorkdayType::NineHours->clockMinutes()
                        ? WorkdayType::NineHours
                        : WorkdayType::EightHours;

                    $dayOffs = collect(preg_split('/\s*,\s*/', (string) ($row->day_off ?? ''), -1, PREG_SPLIT_NO_EMPTY))
                        ->map(fn (string $day): string => ucfirst(strtolower(trim($day))))
                        ->filter(fn (string $day): bool => in_array($day, [
                            'Monday',
                            'Tuesday',
                            'Wednesday',
                            'Thursday',
                            'Friday',
                            'Saturday',
                            'Sunday',
                        ], true))
                        ->unique()
                        ->values()
                        ->all();

                    DB::table('employee_plotting_schedules')
                        ->where('id', $row->id)
                        ->update([
                            'workday_type' => $workdayType->value,
                            'paid_work_minutes' => $workdayType->paidMinutes(),
                            'lunch_break_minutes' => $workdayType->lunchMinutes(),
                            'day_offs' => $dayOffs === [] ? null : json_encode($dayOffs, JSON_THROW_ON_ERROR),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('employee_plotting_schedules')) {
            return;
        }

        Schema::table('employee_plotting_schedules', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('employee_plotting_schedules', 'day_offs') ? 'day_offs' : null,
                Schema::hasColumn('employee_plotting_schedules', 'lunch_break_minutes') ? 'lunch_break_minutes' : null,
                Schema::hasColumn('employee_plotting_schedules', 'paid_work_minutes') ? 'paid_work_minutes' : null,
                Schema::hasColumn('employee_plotting_schedules', 'workday_type') ? 'workday_type' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        $this->restoreLegacyColumns();
    }

    private function expandLegacyColumns(): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            if (Schema::hasColumn('employee_plotting_schedules', 'status')) {
                DB::statement(
                    "ALTER TABLE employee_plotting_schedules MODIFY status VARCHAR(30) NOT NULL DEFAULT 'scheduled'"
                );
            }

            if (Schema::hasColumn('employee_plotting_schedules', 'day_off')) {
                DB::statement(
                    'ALTER TABLE employee_plotting_schedules MODIFY day_off VARCHAR(100) NULL'
                );
            }

            return;
        }

        Schema::table('employee_plotting_schedules', function (Blueprint $table): void {
            if (Schema::hasColumn('employee_plotting_schedules', 'status')) {
                $table->string('status', 30)->default('scheduled')->change();
            }

            if (Schema::hasColumn('employee_plotting_schedules', 'day_off')) {
                $table->string('day_off', 100)->nullable()->change();
            }
        });
    }

    private function restoreLegacyColumns(): void
    {
        if (Schema::hasColumn('employee_plotting_schedules', 'status')) {
            DB::table('employee_plotting_schedules')
                ->where('status', 'inactive')
                ->update(['status' => 'rest_day']);
        }

        if (Schema::hasColumn('employee_plotting_schedules', 'day_off')) {
            DB::table('employee_plotting_schedules')
                ->select(['id', 'day_off'])
                ->whereNotNull('day_off')
                ->orderBy('id')
                ->chunkById(200, function ($rows): void {
                    foreach ($rows as $row) {
                        $firstDay = trim(explode(',', (string) $row->day_off)[0] ?? '');

                        DB::table('employee_plotting_schedules')
                            ->where('id', $row->id)
                            ->update(['day_off' => $firstDay !== '' ? $firstDay : null]);
                    }
                });
        }

        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            if (Schema::hasColumn('employee_plotting_schedules', 'status')) {
                DB::statement(
                    "ALTER TABLE employee_plotting_schedules MODIFY status ENUM('scheduled','rest_day','leave','holiday') NOT NULL DEFAULT 'scheduled'"
                );
            }

            if (Schema::hasColumn('employee_plotting_schedules', 'day_off')) {
                DB::statement(
                    'ALTER TABLE employee_plotting_schedules MODIFY day_off VARCHAR(20) NULL'
                );
            }

            return;
        }

        Schema::table('employee_plotting_schedules', function (Blueprint $table): void {
            if (Schema::hasColumn('employee_plotting_schedules', 'status')) {
                $table->string('status')->default('scheduled')->change();
            }

            if (Schema::hasColumn('employee_plotting_schedules', 'day_off')) {
                $table->string('day_off', 20)->nullable()->change();
            }
        });
    }

    private function clockMinutes(?string $timeIn, ?string $timeOut): int
    {
        if (! $timeIn || ! $timeOut) {
            return 0;
        }

        $start = strtotime($timeIn);
        $end = strtotime($timeOut);

        if ($start === false || $end === false) {
            return 0;
        }

        if ($end <= $start) {
            $end += 86400;
        }

        return (int) (($end - $start) / 60);
    }
};
