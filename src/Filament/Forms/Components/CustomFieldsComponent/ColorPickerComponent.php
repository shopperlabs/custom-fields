<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Field;
use Relaticle\CustomFields\Models\CustomField;

final readonly class ColorPickerComponent implements AttributeComponentInterface
{
    public function __construct(private Configurator $configurator) {}

    public function make(CustomField $customField): Field
    {
        $field = ColorPicker::make("custom_fields.{$customField->code}");

        return $this->configurator->configure($field, $customField);
    }
}
