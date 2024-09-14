<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Forms\Components\CustomAttributes;

use Filament\Forms\Components\Field;
use Illuminate\Support\Carbon;
use ManukMinasyan\FilamentCustomField\Data\ValidationRuleData;
use ManukMinasyan\FilamentCustomField\Enums\AttributeType;
use ManukMinasyan\FilamentCustomField\Models\Attribute;
use Spatie\LaravelData\DataCollection;

final readonly class CommonAttributeConfigurator
{
    /**
     * @template T of Field
     *
     * @param  T  $field
     * @return T
     */
    public function configure(Field $field, Attribute $attribute): Field
    {
        return $field
            ->label($attribute->name)
            ->reactive()
            ->afterStateHydrated(function ($component, $state, $record) use ($attribute): void {
                $value = $record?->getCustomAttributeValue($attribute->code);
                $component->state(match ($attribute->type) {
                    AttributeType::DATE => $value instanceof Carbon ? $value->toDateString() : $value,
                    AttributeType::DATETIME => $value instanceof Carbon ? $value->toDateTimeString() : $value,
                    default => $value,
                });
            })
            ->dehydrated(fn ($state): bool => $state !== null && $state !== '')
            ->rules($this->convertRulesToFilamentFormat($attribute->validation_rules));
    }

    /**
     * Converts validation rules from a collection to an array in the format expected by Filament.
     *
     * @param  DataCollection<int, ValidationRuleData>|null  $rules  The validation rules to convert.
     * @return array<string, string> The converted rules.
     */
    private function convertRulesToFilamentFormat(?DataCollection $rules): array
    {
        if (! $rules instanceof DataCollection || $rules->toCollection()->isEmpty()) {
            return [];
        }

        return $rules->toCollection()->map(function ($ruleData): string {
            if ($ruleData->parameters === []) {
                return $ruleData->name;
            }

            return $ruleData->name.':'.implode(',', $ruleData->parameters);
        })->toArray();
    }
}
