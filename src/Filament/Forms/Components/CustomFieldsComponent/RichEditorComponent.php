<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\RichEditor;
use ManukMinasyan\FilamentCustomField\Models\CustomField;

final readonly class RichEditorComponent implements AttributeComponentInterface
{
    public function __construct(private Configurator $configurator) {}

    public function make(CustomField $customField): Field
    {
        $field = RichEditor::make("custom_fields.{$customField->code}");

        return $this->configurator->configure($field, $customField);
    }
}
