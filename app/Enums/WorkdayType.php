<?php

declare(strict_types=1);

namespace App\Enums;

enum WorkdayType: string
{
    case EightHours = 'eight_hours';
    case NineHours = 'nine_hours';
    case StraightEightHours = 'straight_eight';

    public function label(): string
    {
        return match ($this) {
            self::EightHours => '8 work hours + 1 hour lunch',
            self::NineHours => '9 work hours + 1 hour lunch',
            self::StraightEightHours => '8 work hours straight (no lunch)',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::EightHours => '8 hrs + 1 hr lunch',
            self::NineHours => '9 hrs + 1 hr lunch',
            self::StraightEightHours => '8 hrs straight, no lunch',
        };
    }

    public function paidHours(): int
    {
        return match ($this) {
            self::EightHours, self::StraightEightHours => 8,
            self::NineHours => 9,
        };
    }

    public function paidMinutes(): int
    {
        return $this->paidHours() * 60;
    }

    public function lunchMinutes(): int
    {
        return $this === self::StraightEightHours ? 0 : 60;
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
