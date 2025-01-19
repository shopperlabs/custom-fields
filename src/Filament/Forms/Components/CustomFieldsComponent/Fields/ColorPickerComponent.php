<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Field;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\FieldComponentInterface;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\FieldConfigurator;
use Relaticle\CustomFields\Models\CustomField;

final readonly class ColorPickerComponent implements FieldComponentInterface
{
    public function __construct(private FieldConfigurator $configurator) {}

    public function make(CustomField $customField): Field
    {
        $field = ColorPicker::make("custom_fields.{$customField->code}");

        return $this->configurator->configure($field, $customField);
    }
}
