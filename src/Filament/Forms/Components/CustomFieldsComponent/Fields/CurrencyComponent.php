<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\FieldComponentInterface;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\FieldConfigurator;
use Relaticle\CustomFields\Models\CustomField;

final readonly class CurrencyComponent implements FieldComponentInterface
{
    public function __construct(private FieldConfigurator $configurator) {}

    public function make(CustomField $customField): Field
    {
        $field = TextInput::make("custom_fields.{$customField->code}")
            ->prefix('$')
            ->numeric()
            ->inputMode('decimal')
            ->step(0.01)
            ->minValue(0)
            ->default(0)
            ->rules(['numeric', 'min:0'])
            ->formatStateUsing(fn ($state): string => number_format((float) $state, 2))
            ->dehydrateStateUsing(fn ($state) => Str::of($state)->replace(['$', ','], '')->toFloat());

        return $this->configurator->configure($field, $customField);
    }
}
