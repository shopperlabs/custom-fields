<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Forms\Components;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Enums\CustomFieldValidationRule;

final class CustomFieldValidationComponent extends Component
{
    protected string $view = 'filament-forms::components.group';

    public function __construct()
    {
        $this->schema([
            $this->buildValidationRulesRepeater(),
        ]);

        $this->columnSpanFull();
    }

    public static function make(): self
    {
        return app(self::class);
    }

    private function buildValidationRulesRepeater(): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('validation_rules')
            ->label(__('custom-fields::custom-fields.field.form.validation.rules'))
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Select::make('name')
                            ->label(__('custom-fields::custom-fields.field.form.validation.rule'))
                            ->placeholder('Select Rule')
                            ->options(function (Get $get) {
                                $existingRules = $get('../../validation_rules') ?? [];
                                $customFieldType = CustomFieldType::tryFrom($get('../../type'));
                                $allowedRules = $customFieldType instanceof CustomFieldType ? $customFieldType->allowedValidationRules() : [];

                                return collect($allowedRules)
                                    ->reject(fn ($enum): bool => $this->hasDuplicateRule($existingRules, $enum->value))
                                    ->mapWithKeys(fn ($enum) => [$enum->value => $enum->getLabel()])
                                    ->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->required()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?string $old): void {
                                if ($old !== $state) {
                                    $set('parameters', []);
                                }
                            })
                            ->columnSpan(1),
                        Forms\Components\Placeholder::make('description')
                            ->label(__('custom-fields::custom-fields.field.form.validation.description'))
                            ->content(fn (Get $get): string => CustomFieldValidationRule::getDescriptionForRule($get('name')))
                            ->columnSpan(2),
                        $this->buildRuleParametersRepeater(),
                    ]),
            ])
            ->itemLabel(fn (array $state): string => CustomFieldValidationRule::getLabelForRule((string) $state['name'], $state['parameters'] ?? []))
            ->collapsible()
            ->reorderable()
            ->deletable()
            ->hintColor('danger')
            ->addable(fn (Get $get): bool => $get('type') && CustomFieldType::tryFrom($get('type')))
            ->hint(function (Get $get): string {
                $isTypeSelected = $get('type') && CustomFieldType::tryFrom($get('type'));

                return $isTypeSelected ? '' : __('custom-fields::custom-fields.field.form.validation.rules_hint');
            })
            ->hiddenLabel()
            ->defaultItems(0)
            ->addActionLabel(__('custom-fields::custom-fields.field.form.validation.add_rule'))
            ->columnSpanFull();
    }

    private function buildRuleParametersRepeater(): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('parameters')
            ->label(__('custom-fields::custom-fields.field.form.validation.parameters'))
            ->simple(
                Forms\Components\TextInput::make('value')
                    ->label(__('custom-fields::custom-fields.field.form.validation.parameters_value'))
                    ->required()
                    ->hiddenLabel()
                    ->maxLength(255),
            )
            ->columnSpanFull()
            ->visible(fn (Get $get): bool => CustomFieldValidationRule::hasParameterForRule($get('name')))
            ->minItems(1)
            ->maxItems(fn (Get $get): int => CustomFieldValidationRule::getAllowedParametersCountForRule($get('name')))
            ->reorderable(false)
            ->defaultItems(1)
            ->addActionLabel(__('custom-fields::custom-fields.field.form.validation.add_parameter'));
    }

    /**
     * Checks if a validation rule already exists in the array of rules.
     *
     * @param  array<string, array<string, string>>  $rules
     */
    private function hasDuplicateRule(array $rules, string $newRule): bool
    {
        return collect($rules)->contains(fn (array $rule): bool => $rule['name'] === $newRule);
    }
}
