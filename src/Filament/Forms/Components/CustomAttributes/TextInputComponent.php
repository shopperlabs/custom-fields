<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Forms\Components\CustomAttributes;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;
use ManukMinasyan\FilamentCustomField\Models\Attribute;

final readonly class TextInputComponent implements AttributeComponentInterface
{
    public function __construct(private CommonAttributeConfigurator $configurator) {}

    public function make(Attribute $attribute): Field
    {
        $field = TextInput::make("custom_attributes.{$attribute->code}")
            ->maxLength(255)
            ->placeholder(null);

        return $this->configurator->configure($field, $attribute);
    }
}
