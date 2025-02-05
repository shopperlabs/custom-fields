<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Tables\Columns;

use Filament\Tables\Columns\Column;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Models\CustomField;
use RuntimeException;

final class FieldColumnFactory
{
    /**
     * @var array<string, class-string<ColumnInterface>>
     */
    private array $componentMap = [
        CustomFieldType::SELECT->value => SingleValueColumn::class,
        CustomFieldType::MULTI_SELECT->value => MultiValueColumn::class,
        CustomFieldType::CHECKBOX->value => SingleValueColumn::class,
        CustomFieldType::CHECKBOX_LIST->value => MultiValueColumn::class,
        CustomFieldType::TOGGLE->value => IconColumn::class,
        CustomFieldType::TOGGLE_BUTTONS->value => MultiValueColumn::class,
        CustomFieldType::LINK->value => TextColumn::class,
        CustomFieldType::DATE->value => DateTimeColumn::class,
        CustomFieldType::DATE_TIME->value => DateTimeColumn::class,
    ];

    /**
     * @var array<class-string<ColumnInterface>, ColumnInterface>
     */
    private array $instanceCache = [];

    public function __construct(private readonly Container $container)
    {
    }

    public function create(CustomField $customField): Column
    {
        $customFieldType = $customField->type->value;

        if (!isset($this->componentMap[$customFieldType])) {
            throw new InvalidArgumentException("No column registered for custom field type: {$customFieldType}");
        }

        $filterClass = $this->componentMap[$customFieldType];

        if (!isset($this->instanceCache[$filterClass])) {
            $component = $this->container->make($filterClass);

            if (!$component instanceof ColumnInterface) {
                throw new RuntimeException("Component class {$filterClass} must implement FieldColumnInterface");
            }

            $this->instanceCache[$filterClass] = $component;
        } else {
            $component = $this->instanceCache[$filterClass];
        }

        return $component->make($customField)
            ->columnSpan($customField->width->getSpanValue());
    }
}
