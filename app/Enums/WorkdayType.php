<?php

namespace App\Enums;

enum WorkdayType: string
{
    case EightHours = 'eight_hours';
    case NineHours = 'nine_hours';

    public function label(): string
    {
        return match ($this) {
            self::EightHours => '8 work hours + 1 hour lunch',
            self::NineHours => '9 work hours + 1 hour lunch',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::EightHours => '8 hrs + 1 hr lunch',
            self::NineHours => '9 hrs + 1 hr lunch',
        };
    }

    public function paidHours(): int
    {
        return match ($this) {
            self::EightHours => 8,
            self::NineHours => 9,
        };
    }

    public function paidMinutes(): int
    {
        return $this->paidHours() * 60;
    }

    public function lunchMinutes(): int
    {
        return 60;
    }

    public function clockMinutes(): int
    {
        return $this->paidMinutes() + $this->lunchMinutes();
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }

    public static function values(): array
    {
        return array_map(
            static fn (self $type): string => $type->value,
            self::cases()
        );
    }

    public static function fromPaidMinutes(?int $paidMinutes): self
    {
        return $paidMinutes === self::NineHours->paidMinutes()
            ? self::NineHours
            : self::EightHours;
    }
}
