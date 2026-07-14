<?php

namespace App\Enums;

enum JobOrderRepairType: string
{
    case Mechanical = 'mechanical';
    case Electrical = 'electrical';
    case Aircon = 'aircon';
    case BodyRepair = 'body_repair';
    case Repainting = 'repainting';

    public function label(): string
    {
        return match ($this) {
            self::Mechanical => 'Mechanical',
            self::Electrical => 'Electrical',
            self::Aircon => 'Aircon',
            self::BodyRepair => 'Body Repair',
            self::Repainting => 'Repainting',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Mechanical => 'fas fa-gears',
            self::Electrical => 'fas fa-bolt',
            self::Aircon => 'fas fa-snowflake',
            self::BodyRepair => 'fas fa-car-burst',
            self::Repainting => 'fas fa-spray-can-sparkles',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Mechanical => 'badge-subtle-primary text-primary',
            self::Electrical => 'badge-subtle-warning text-warning',
            self::Aircon => 'badge-subtle-info text-info',
            self::BodyRepair => 'badge-subtle-danger text-danger',
            self::Repainting => 'badge-subtle-success text-success',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }

    public static function values(): array
    {
        return collect(self::cases())
            ->map(fn (self $type): string => $type->value)
            ->all();
    }
}
