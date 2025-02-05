<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\FieldComponentInterface;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\FieldConfigurator;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Support\FieldTypeUtils;

final readonly class DateComponent implements FieldComponentInterface
{
    public function __construct(private FieldConfigurator $configurator) {}

    public function make(CustomField $customField): Field
    {
        $field = DatePicker::make("custom_fields.{$customField->code}")
            ->native(FieldTypeUtils::isDatePickerNative())
            ->format(FieldTypeUtils::getDateFormat())
            ->displayFormat(FieldTypeUtils::getDateDisplayFormat())
            ->placeholder(FieldTypeUtils::getDateDisplayFormat());

        return $this->configurator->configure($field, $customField);
    }
}
