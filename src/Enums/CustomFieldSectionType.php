<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Enums;

use Filament\Support\Contracts\HasLabel;

enum CustomFieldSectionType: string implements HasLabel
{
    case SECTION = 'section';
    case FIELDSET = 'fieldset';
    case HEADLESS = 'headless';

    public function getLabel(): string
    {
        return match ($this) {
            self::SECTION => 'Section',
            self::FIELDSET => 'Fieldset',
            self::HEADLESS => 'Headless',
        };
    }
}
