<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Relaticle\CustomFields\Models\CustomField;

final readonly class TextareaAttributeComponent implements AttributeComponentInterface
{
    public function __construct(private Configurator $configurator) {}

    public function make(CustomField $customField): Field
    {
        $field = Textarea::make("custom_fields.{$customField->code}")
            ->rows(3)
            ->maxLength(50000)
            ->placeholder(null);

        return $this->configurator->configure($field, $customField);
    }
}
