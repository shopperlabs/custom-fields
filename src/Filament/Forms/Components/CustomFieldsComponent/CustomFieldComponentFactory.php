<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Field;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use ManukMinasyan\FilamentCustomField\Enums\CustomFieldType;
use ManukMinasyan\FilamentCustomField\Models\CustomField;
use RuntimeException;

final class CustomFieldComponentFactory
{
    /**
     * @var array<string, class-string<AttributeComponentInterface>>
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
        CustomFieldType::TEXTAREA->value => TextareaAttributeComponent::class,
        CustomFieldType::CURRENCY->value => CurrencyComponent::class,
        CustomFieldType::DATE->value => DateComponent::class,
        CustomFieldType::DATE_TIME->value => DateTimeComponent::class,
        CustomFieldType::TOGGLE->value => ToggleComponent::class,
        CustomFieldType::RADIO->value => RadioComponent::class,
        CustomFieldType::SELECT->value => SelectComponent::class,
        CustomFieldType::MULTI_SELECT->value => MultiSelectComponent::class,
    ];

    /**
     * @var array<class-string<AttributeComponentInterface>, AttributeComponentInterface>
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

            if (! $component instanceof AttributeComponentInterface) {
                throw new RuntimeException("Component class {$componentClass} must implement AttributeComponentInterface");
            }

            $this->instanceCache[$componentClass] = $component;
        } else {
            $component = $this->instanceCache[$componentClass];
        }

        return $component->make($customField);
    }
}
