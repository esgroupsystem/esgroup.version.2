<?php

namespace App\Enums;

enum JobOrderStatus: string
{
    case Standby = 'standby';
    case WaitingParts = 'waiting_parts';
    case OnGoingRepair = 'on_going_repair';
    case Operational = 'operational';

    public function label(): string
    {
        return match ($this) {
            self::Standby => 'Standby',
            self::WaitingParts => 'Waiting Parts',
            self::OnGoingRepair => 'On Going Repair',
            self::Operational => 'Operational',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Standby => 'badge-subtle-secondary text-secondary',
            self::WaitingParts => 'badge-subtle-danger text-danger',
            self::OnGoingRepair => 'badge-subtle-warning text-warning',
            self::Operational => 'badge-subtle-success text-success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Standby => 'fas fa-pause-circle',
            self::WaitingParts => 'fas fa-box-open',
            self::OnGoingRepair => 'fas fa-tools',
            self::Operational => 'fas fa-check-circle',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Standby => 'Job order has been created but repair work has not started yet.',
            self::WaitingParts => 'Repair is delayed because required parts are not yet available.',
            self::OnGoingRepair => 'Bus is currently under maintenance or active repair.',
            self::Operational => 'Repair is completed and the bus is ready for operation.',
        };
    }

    public function countsAsDowntime(): bool
    {
        return $this !== self::Operational;
    }

    public static function downtimeStatuses(): array
    {
        return [
            self::Standby,
            self::WaitingParts,
            self::OnGoingRepair,
        ];
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }

    public static function values(): array
    {
        return collect(self::cases())
            ->map(fn (self $status): string => $status->value)
            ->all();
    }
}
