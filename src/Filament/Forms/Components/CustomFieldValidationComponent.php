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
                                $fieldType = $get('../../type');
                                if (empty($fieldType)) {
                                    return [];
                                }
                                $customFieldType = CustomFieldType::tryFrom($fieldType);
                                $allowedRules = $customFieldType instanceof CustomFieldType ? $customFieldType->allowedValidationRules() : [];

                                return collect($allowedRules)
                                    ->reject(fn ($enum): bool => $this->hasDuplicateRule($existingRules, $enum->value))
                                    ->mapWithKeys(fn ($enum) => [$enum->value => $enum->getLabel()])
                                    ->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?string $old): void {
                                if ($old !== $state) {
                                    $set('parameters', []);
                                    
                                    if (empty($state)) {
                                        return;
                                    }
                            
                                    // Create appropriate number of parameters based on rule requirements
                                    $rule = CustomFieldValidationRule::tryFrom($state);
                                    if ($rule && $rule->allowedParameterCount() > 0) {
                                        $paramCount = $rule->allowedParameterCount();
                                        $parameters = array_fill(0, $paramCount, ['value' => '']);
                                        $set('parameters', $parameters);
                                    }
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
                    ->maxLength(255)
                    ->rules(function (Get $get, $record, $state, Forms\Components\Component $component): array {
                        $ruleName = $get('../../name');
                        $parameterIndex = $this->getParameterIndex($component);
                        return CustomFieldValidationRule::getParameterValidationRuleFor($ruleName, $parameterIndex);
                    })
                    ->hint(function (Get $get, Forms\Components\Component $component): string {
                        $ruleName = $get('../../name');
                        if (empty($ruleName)) {
                            return '';
                        }
                        $parameterIndex = $this->getParameterIndex($component);
                        
                        return CustomFieldValidationRule::getParameterHelpTextFor($ruleName, $parameterIndex);
                    })
                    ->afterStateHydrated(function (Get $get, Set $set, $state, Forms\Components\Component $component): void {
                        if ($state === null) {
                            return;
                        }
                        
                        $ruleName = $get('../../name');
                        if (empty($ruleName)) {
                            return;
                        }
                        $parameterIndex = $this->getParameterIndex($component);
                        
                        $set('value', $this->normalizeParameterValue($ruleName, (string) $state, $parameterIndex));
                    })
                    ->dehydrateStateUsing(function (Get $get, $state, Forms\Components\Component $component) {
                        if ($state === null) {
                            return null;
                        }
                        
                        $ruleName = $get('../../name');
                        if (empty($ruleName)) {
                            return $state;
                        }
                        $parameterIndex = $this->getParameterIndex($component);
                        
                        return $this->normalizeParameterValue($ruleName, (string) $state, $parameterIndex);
                    }),
            )
            ->columnSpanFull()
            ->visible(fn (Get $get): bool => CustomFieldValidationRule::hasParameterForRule($get('name')))
            ->minItems(function (Get $get): int {
                $ruleName = $get('name');
                if (empty($ruleName)) {
                    return 1;
                }
                $rule = CustomFieldValidationRule::tryFrom($ruleName);
                
                // For rules with specific parameter counts, ensure we have the right minimum
                if ($rule && $rule->allowedParameterCount() > 0) {
                    return $rule->allowedParameterCount();
                }
                
                return 1;
            })
            ->maxItems(fn (Get $get): int => CustomFieldValidationRule::getAllowedParametersCountForRule($get('name')))
            ->reorderable(false)
            ->deletable(function (Get $get): bool {
                $ruleName = $get('name');
                if (empty($ruleName)) {
                    return true;
                }
                $rule = CustomFieldValidationRule::tryFrom($ruleName);
            
                // For rules with specific parameter counts, don't allow deleting if it would go below required count
                return !($rule && $rule->allowedParameterCount() > 0 && count($get('parameters') ?? []) <= $rule->allowedParameterCount());
            })
            ->defaultItems(function (Get $get): int {
                $ruleName = $get('name');
                if (empty($ruleName)) {
                    return 1;
                }
                $rule = CustomFieldValidationRule::tryFrom($ruleName);
                
                // For rules with specific parameter counts, create the right number by default
                if ($rule && $rule->allowedParameterCount() > 0) {
                    return $rule->allowedParameterCount();
                }
                
                return 1;
            })
            ->hint(function (Get $get) {
                $ruleName = $get('name');
                if (empty($ruleName)) {
                    return null;
                }
                $rule = CustomFieldValidationRule::tryFrom($ruleName);
                $parameters = $get('parameters') ?? [];
                
                // Validate that rules have the correct number of parameters
                if ($rule && $rule->allowedParameterCount() > 0 && count($parameters) < $rule->allowedParameterCount()) {
                    $requiredCount = $rule->allowedParameterCount();
                    
                    // Special case handling for known rules
                    if ($requiredCount === 2) {
                        return match($rule) {
                            CustomFieldValidationRule::BETWEEN => 
                                __('custom-fields::custom-fields.validation.between_validation_error'),
                            CustomFieldValidationRule::DIGITS_BETWEEN => 
                                __('custom-fields::custom-fields.validation.digits_between_validation_error'),
                            CustomFieldValidationRule::DECIMAL => 
                                __('custom-fields::custom-fields.validation.decimal_validation_error'),
                            default => 
                                __('custom-fields::custom-fields.validation.parameter_missing', ['count' => $requiredCount]),
                        };
                    }
                    
                    // Generic message for other parameter counts
                    return __('custom-fields::custom-fields.validation.parameter_missing', ['count' => $requiredCount]);
                }
                
                return null;
            })
            ->hintColor('danger')
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
    
    /**
     * Normalize a parameter value based on the validation rule type.
     * 
     * @param string|null $ruleName The validation rule name
     * @param string $value The parameter value to normalize
     * @param int $parameterIndex The index of the parameter (0-based)
     * @return string The normalized parameter value
     */
    private function normalizeParameterValue(?string $ruleName, string $value, int $parameterIndex = 0): string
    {
        return CustomFieldValidationRule::normalizeParameterValue($ruleName, $value, $parameterIndex);
    }
    
    /**
     * Get the parameter index from a component within a repeater.
     *
     * @param Forms\Components\Component $component The component to get the index for
     * @return int The zero-based index of the parameter
     */
    private function getParameterIndex(Forms\Components\Component $component): int
    {
        $statePath = $component->getStatePath();
        
        if (preg_match('/parameters\.(\d+)\./', $statePath, $matches)) {
            return (int) $matches[1];
        }
        
        return 0;
    }
}