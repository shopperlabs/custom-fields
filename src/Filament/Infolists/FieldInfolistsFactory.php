<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Infolists;

use Filament\Infolists\Components\Entry;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Filament\Infolists\Fields\DateTimeEntry;
use Relaticle\CustomFields\Filament\Infolists\Fields\TextEntry;
use Relaticle\CustomFields\Models\CustomField;
use RuntimeException;

final class FieldInfolistsFactory
{
    /**
     * @var array<string, class-string<FieldInfolistsComponentInterface>>
     */
    private array $componentMap = [
        CustomFieldType::TEXT->value => TextEntry::class,
        CustomFieldType::TOGGLE->value => TextEntry::class,
        CustomFieldType::LINK->value => TextEntry::class,
        CustomFieldType::SELECT->value => TextEntry::class,
        CustomFieldType::NUMBER->value => TextEntry::class,
        CustomFieldType::CHECKBOX->value => TextEntry::class,
        CustomFieldType::CHECKBOX_LIST->value => TextEntry::class,
        CustomFieldType::RADIO->value => TextEntry::class,
        CustomFieldType::RICH_EDITOR->value => TextEntry::class,
        CustomFieldType::MARKDOWN_EDITOR->value => TextEntry::class,
        CustomFieldType::TAGS_INPUT->value => TextEntry::class,
        CustomFieldType::COLOR_PICKER->value => TextEntry::class,
        CustomFieldType::TOGGLE_BUTTONS->value => TextEntry::class,
        CustomFieldType::TEXTAREA->value => TextEntry::class,
        CustomFieldType::CURRENCY->value => TextEntry::class,
        CustomFieldType::DATE->value => TextEntry::class,
        CustomFieldType::MULTI_SELECT->value => TextEntry::class,
        CustomFieldType::DATE_TIME->value => DateTimeEntry::class,
    ];

    /**
     * @var array<class-string<FieldInfolistsComponentInterface>, FieldInfolistsComponentInterface>
     */
    private array $instanceCache = [];

    public function __construct(private readonly Container $container) {}

    public function create(CustomField $customField): Entry
    {
        $customFieldType = $customField->type->value;

        if (! isset($this->componentMap[$customFieldType])) {
            throw new InvalidArgumentException("No infolists component registered for custom field type: {$customFieldType}");
        }

        $componentClass = $this->componentMap[$customFieldType];

        if (! isset($this->instanceCache[$componentClass])) {
            $component = $this->container->make($componentClass);

            if (! $component instanceof FieldInfolistsComponentInterface) {
                throw new RuntimeException("Infolists component class {$componentClass} must implement FieldInfolistsComponentInterface");
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
