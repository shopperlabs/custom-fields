<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Field;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\CheckboxComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\CheckboxListComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\ColorPickerComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\CurrencyComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\DateComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\DateTimeComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\LinkComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\MarkdownEditorComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\MultiSelectComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\NumberComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\RadioComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\RichEditorComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\SelectComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\TagsInputComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\TextareaFieldComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\TextInputComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\ToggleButtonsComponent;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\Fields\ToggleComponent;
use Relaticle\CustomFields\Models\CustomField;
use RuntimeException;

final class FieldComponentFactory
{
    /**
     * @var array<string, class-string<FieldComponentInterface>>
     */
    private array $componentMap = [
        CustomFieldType::TEXT->value => TextInputComponent::class,
        CustomFieldType::NUMBER->value => NumberComponent::class,
        CustomFieldType::CHECKBOX->value => CheckboxComponent::class,
        CustomFieldType::CHECKBOX_LIST->value => CheckboxListComponent::class,
        CustomFieldType::RICH_EDITOR->value => RichEditorComponent::class,
        CustomFieldType::MARKDOWN_EDITOR->value => MarkdownEditorComponent::class,
        CustomFieldType::TOGGLE_BUTTONS->value => ToggleButtonsComponent::class,
        CustomFieldType::TAGS_INPUT->value => TagsInputComponent::class,
        CustomFieldType::LINK->value => LinkComponent::class,
        CustomFieldType::COLOR_PICKER->value => ColorPickerComponent::class,
        CustomFieldType::TEXTAREA->value => TextareaFieldComponent::class,
        CustomFieldType::CURRENCY->value => CurrencyComponent::class,
        CustomFieldType::DATE->value => DateComponent::class,
        CustomFieldType::DATE_TIME->value => DateTimeComponent::class,
        CustomFieldType::TOGGLE->value => ToggleComponent::class,
        CustomFieldType::RADIO->value => RadioComponent::class,
        CustomFieldType::SELECT->value => SelectComponent::class,
        CustomFieldType::MULTI_SELECT->value => MultiSelectComponent::class,
    ];

    /**
     * @var array<class-string<FieldComponentInterface>, FieldComponentInterface>
     */
    private array $instanceCache = [];

    public function __construct(private readonly Container $container) {}

    public function create(CustomField $customField): Field
    {
        $customFieldType = $customField->type->value;

        if (! isset($this->componentMap[$customFieldType])) {
            throw new InvalidArgumentException("No component registered for custom field type: {$customFieldType}");
        }

        $componentClass = $this->componentMap[$customFieldType];

        if (! isset($this->instanceCache[$componentClass])) {
            $component = $this->container->make($componentClass);

            if (! $component instanceof FieldComponentInterface) {
                throw new RuntimeException("Component class {$componentClass} must implement FieldComponentInterface");
            }

            $this->instanceCache[$componentClass] = $component;
        } else {
            $component = $this->instanceCache[$componentClass];
        }

        return $component->make($customField)
            ->columnSpan($customField->width->getSpanValue())
            ->inlineLabel(false);
    }
}
