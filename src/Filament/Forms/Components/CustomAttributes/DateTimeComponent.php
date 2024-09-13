<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Forms\Components\CustomAttributes;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use ManukMinasyan\FilamentCustomField\Models\Attribute;

final readonly class DateTimeComponent implements AttributeComponentInterface
{
    public function __construct(private CommonAttributeConfigurator $configurator) {}

    public function make(Attribute $attribute): Field
    {
        $field = DateTimePicker::make("custom_attributes.{$attribute->code}");

        return $this->configurator->configure($field, $attribute);
    }
}
