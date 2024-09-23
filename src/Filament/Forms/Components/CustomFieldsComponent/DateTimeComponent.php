<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Relaticle\CustomFields\Models\CustomField;

final readonly class DateTimeComponent implements AttributeComponentInterface
{
    public function __construct(private Configurator $configurator) {}

    public function make(CustomField $customField): Field
    {
        $field = DateTimePicker::make("custom_fields.{$customField->code}");

        return $this->configurator->configure($field, $customField);
    }
}
