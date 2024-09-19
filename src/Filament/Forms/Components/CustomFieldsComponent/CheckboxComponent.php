<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Field;
use ManukMinasyan\FilamentCustomField\Models\CustomField;

final readonly class CheckboxComponent implements AttributeComponentInterface
{
    public function __construct(private Configurator $configurator) {}

    public function make(CustomField $customField): Field
    {
        $field = Checkbox::make("custom_fields.{$customField->code}")->inline(false);

        return $this->configurator->configure($field, $customField);
    }
}
