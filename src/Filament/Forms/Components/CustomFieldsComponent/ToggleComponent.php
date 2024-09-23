<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Toggle;
use Relaticle\CustomFields\Models\CustomField;

final readonly class ToggleComponent implements AttributeComponentInterface
{
    public function __construct(private Configurator $configurator)
    {
    }

    public function make(CustomField $customField): Field
    {
        $field = Toggle::make("custom_fields.{$customField->code}")
            ->onColor('success')
            ->offColor('danger')
            ->inline(false);

        return $this->configurator->configure($field, $customField);
    }
}
