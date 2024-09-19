<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Collection;

enum CustomFieldType: string implements HasLabel
{
    case TEXT = 'text';
    case NUMBER = 'number';
    case LINK = 'link';
    case SELECT = 'select';
    case CHECKBOX = 'checkbox';
    case CHECKBOX_LIST = 'checkbox-list';
    case RADIO = 'radio';
    case RICH_EDITOR = 'rich-editor';
    case MARKDOWN_EDITOR = 'markdown-editor';
    case TAGS_INPUT = 'tags-input';
    case COLOR_PICKER = 'color-picker';
    case TOGGLE = 'toggle';
    case TOGGLE_BUTTONS = 'toggle-buttons';
    case TEXTAREA = 'textarea';
    case CURRENCY = 'currency';
    case DATE = 'date';
    case DATE_TIME = 'date-time';
    case MULTI_SELECT = 'multi-select';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::TEXT->value => 'Text',
            self::NUMBER->value => 'Number',
            self::LINK->value => 'Link',
            self::TEXTAREA->value => 'Textarea',
            self::CURRENCY->value => 'Currency',
            self::DATE->value => 'Date',
            self::DATE_TIME->value => 'Date and Time',
            self::TOGGLE->value => 'Toggle',
            self::TOGGLE_BUTTONS->value => 'Toggle buttons',
            self::SELECT->value => 'Select',
            self::CHECKBOX->value => 'Checkbox',
            self::CHECKBOX_LIST->value => 'Checkbox list',
            self::RADIO->value => 'Radio',
            self::RICH_EDITOR->value => 'Rich editor',
            self::MARKDOWN_EDITOR->value => 'Markdown editor',
            self::TAGS_INPUT->value => 'Tags input',
            self::COLOR_PICKER->value => 'Color picker',
            self::MULTI_SELECT->value => 'Multi-select',
        ];
    }

    public static function icons(): array
    {
        return [
            self::TEXTAREA->value => 'mdi-form-textbox',
            self::NUMBER->value => 'mdi-numeric-7-box',
            self::LINK->value => 'mdi-link-variant',
            self::TEXT->value => 'mdi-form-textbox',
            self::CURRENCY->value => 'mdi-currency-usd',
            self::DATE->value => 'mdi-calendar',
            self::DATE_TIME->value => 'mdi-calendar-clock',
            self::TOGGLE->value => 'mdi-toggle-switch',
            self::TOGGLE_BUTTONS->value => 'mdi-toggle-switch',
            self::SELECT->value => 'mdi-form-select',
            self::CHECKBOX->value => 'mdi-checkbox-marked',
            self::CHECKBOX_LIST->value => 'mdi-checkbox-multiple-marked',
            self::RADIO->value => 'mdi-radiobox-marked',
            self::RICH_EDITOR->value => 'mdi-format-text',
            self::MARKDOWN_EDITOR->value => 'mdi-format-text',
            self::TAGS_INPUT->value => 'mdi-tag-multiple',
            self::COLOR_PICKER->value => 'mdi-palette',
            self::MULTI_SELECT->value => 'mdi-form-dropdown',
        ];
    }

    /**
     * @return array
     */
    public static function optionables(): Collection
    {
        return collect([
            self::MULTI_SELECT,
            self::SELECT,
            self::CHECKBOX_LIST,
            self::TAGS_INPUT,
            self::TOGGLE_BUTTONS,
            self::RADIO,
        ]);
    }

    public function getIcon(): string
    {
        return self::icons()[$this->value];
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
            self::CURRENCY => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::NUMERIC,
                CustomFieldValidationRule::MIN,
                CustomFieldValidationRule::MAX,
                CustomFieldValidationRule::BETWEEN,
                CustomFieldValidationRule::DECIMAL,
            ],
            self::DATE, self::DATE_TIME => [
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
            self::MULTI_SELECT => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::ARRAY,
                CustomFieldValidationRule::MIN,
                CustomFieldValidationRule::MAX,
                CustomFieldValidationRule::BETWEEN,
                CustomFieldValidationRule::IN,
            ],
            self::NUMBER => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::NUMERIC,
                CustomFieldValidationRule::MIN,
                CustomFieldValidationRule::MAX,
                CustomFieldValidationRule::BETWEEN,
                CustomFieldValidationRule::INTEGER,
            ],
            self::LINK => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::URL,
            ],
            self::CHECKBOX => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::BOOLEAN,
            ],
            self::CHECKBOX_LIST => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::ARRAY,
                CustomFieldValidationRule::MIN,
                CustomFieldValidationRule::MAX,
                CustomFieldValidationRule::BETWEEN,
            ],
            self::RADIO => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::IN,
            ],
            self::RICH_EDITOR => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::STRING,
                CustomFieldValidationRule::MIN,
                CustomFieldValidationRule::MAX,
                CustomFieldValidationRule::BETWEEN,
            ],
            self::MARKDOWN_EDITOR => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::STRING,
                CustomFieldValidationRule::MIN,
                CustomFieldValidationRule::MAX,
                CustomFieldValidationRule::BETWEEN,
            ],
            self::TAGS_INPUT => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::ARRAY,
                CustomFieldValidationRule::MIN,
                CustomFieldValidationRule::MAX,
                CustomFieldValidationRule::BETWEEN,
            ],
            self::COLOR_PICKER => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::STRING,
            ],
            self::TOGGLE_BUTTONS => [
                CustomFieldValidationRule::REQUIRED,
                CustomFieldValidationRule::BOOLEAN,
            ],
        };
    }
}
