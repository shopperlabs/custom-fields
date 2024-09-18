<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Field;
use Illuminate\Support\Carbon;
use ManukMinasyan\FilamentCustomField\Data\ValidationRuleData;
use ManukMinasyan\FilamentCustomField\Enums\CustomFieldType;
use ManukMinasyan\FilamentCustomField\Models\CustomField;
use Spatie\LaravelData\DataCollection;

final readonly class Configurator
{
    /**
     * @template T of Field
     *
     * @param  T  $field
     * @return T
     */
    public function configure(Field $field, CustomField $customField): Field
    {
        return $field
            ->label($customField->name)
            ->reactive()
            ->afterStateHydrated(function ($component, $state, $record) use ($customField): void {
                $value = $record?->getCustomFieldValue($customField->code);
                $component->state(match ($customField->type) {
                    CustomFieldType::DATE => $value instanceof Carbon ? $value->toDateString() : $value,
                    CustomFieldType::DATETIME => $value instanceof Carbon ? $value->toDateTimeString() : $value,
                    default => $value,
                });
            })
            ->dehydrated(fn ($state): bool => $state !== null && $state !== '')
            ->rules($this->convertRulesToFilamentFormat($customField->validation_rules));
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
