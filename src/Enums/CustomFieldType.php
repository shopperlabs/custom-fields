<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Enums;

use Filament\Support\Contracts\HasLabel;

enum CustomFieldType: string implements HasLabel
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case PRICE = 'price';
    case DATE = 'date';
    case DATETIME = 'date_time';
    case TOGGLE = 'boolean';
    case SELECT = 'select';
    case MULTISELECT = 'multiselect';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::TEXT->value => 'Text',
            self::TEXTAREA->value => 'Textarea',
            self::PRICE->value => 'Price',
            self::DATE->value => 'Date',
            self::DATETIME->value => 'Date and Time',
            self::TOGGLE->value => 'Toggle',
            self::SELECT->value => 'Select',
            self::MULTISELECT->value => 'Multiselect',
        ];
    }

    public function getLabel(): ?string
    {
        return self::options()[$this->value];
    }

    /**
     * @return array<int, CustomFieldValidationRule>
     */
    public function allowedValidationRules(): array
    {
        return match ($this) {
            self::TEXT => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::MIN,
                CustomFieldValidationRule::MAX,
                CustomFieldValidationRule::BETWEEN,
                CustomFieldValidationRule::REGEX,
                CustomFieldValidationRule::ALPHA,
                CustomFieldValidationRule::ALPHA_NUM,
                CustomFieldValidationRule::ALPHA_DASH,
                CustomFieldValidationRule::STRING,
            ],
            self::TEXTAREA => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::MIN,
                CustomFieldValidationRule::MAX,
                CustomFieldValidationRule::BETWEEN,
                CustomFieldValidationRule::STRING,
            ],
            self::PRICE => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::NUMERIC,
                CustomFieldValidationRule::MIN,
                CustomFieldValidationRule::MAX,
                CustomFieldValidationRule::BETWEEN,
                CustomFieldValidationRule::DECIMAL,
            ],
            self::DATE, self::DATETIME => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::DATE,
                CustomFieldValidationRule::AFTER,
                CustomFieldValidationRule::AFTER_OR_EQUAL,
                CustomFieldValidationRule::BEFORE,
                CustomFieldValidationRule::BEFORE_OR_EQUAL,
                CustomFieldValidationRule::DATE_FORMAT,
            ],
            self::TOGGLE => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::BOOLEAN,
            ],
            self::SELECT => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::IN,
            ],
            self::MULTISELECT => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::ARRAY,
                CustomFieldValidationRule::MIN,
                CustomFieldValidationRule::MAX,
                CustomFieldValidationRule::BETWEEN,
                CustomFieldValidationRule::IN,
            ],
        };
    }
}
