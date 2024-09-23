<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\ToggleButtons;
use Relaticle\CustomFields\Models\CustomField;

final readonly class ToggleButtonsComponent implements AttributeComponentInterface
{
    public function __construct(private Configurator $configurator)
    {
    }

    public function make(CustomField $customField): Field
    {
        $field = ToggleButtons::make("custom_fields.{$customField->code}")->inline(false);

        $field->options($customField->options->pluck('name', 'id')->all());

        return $this->configurator->configure($field, $customField);
    }
}
