<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Enums;

enum CustomFieldWidth: string
{
    case _25 = '25';
    case _33 = '33';
    case _50 = '50';
    case _66 = '66';
    case _75 = '75';
    case _100 = '100';

    public function getSpanValue(): int
    {
        return match ($this) {
            self::_25 => 3,
            self::_33 => 4,
            self::_50 => 6,
            self::_66 => 8,
            self::_75 => 9,
            default => 12,
        };
    }
}
