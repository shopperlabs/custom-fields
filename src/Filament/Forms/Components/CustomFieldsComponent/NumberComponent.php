<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;
use ManukMinasyan\FilamentCustomField\Models\CustomField;

final readonly class NumberComponent implements AttributeComponentInterface
{
    public function __construct(private Configurator $configurator) {}

    public function make(CustomField $customField): Field
    {
        $field = TextInput::make("custom_fields.{$customField->code}")
            ->numeric()
            ->placeholder(null);

        return $this->configurator->configure($field, $customField);
    }
}
