<?php

declare(strict_types=1);

namespace App\Enums;

enum BenefitProgram: string
{
    case Sss = 'sss';
    case PhilHealth = 'philhealth';
    case PagIbig = 'pagibig';

    public function label(): string
    {
        return match ($this) {
            self::Sss => 'SSS',
            self::PhilHealth => 'PhilHealth',
            self::PagIbig => 'Pag-IBIG',
        };
    }
}
