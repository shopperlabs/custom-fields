<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use ManukMinasyan\FilamentCustomField\Models\CustomField;
use ManukMinasyan\FilamentCustomField\Services\FilamentResourceService;

final readonly class TagsInputComponent implements AttributeComponentInterface
{
    public function __construct(private Configurator $configurator)
    {
    }

    public function make(CustomField $customField): Field
    {
        $field = TagsInput::make("custom_fields.{$customField->code}");

        if ($customField->lookup_type) {
            $entityInstance = FilamentResourceService::getModelInstance($customField->lookup_type);
            $recordTitleAttribute = FilamentResourceService::getRecordTitleAttribute($customField->lookup_type);

            $suggestions = $entityInstance::query()->pluck($recordTitleAttribute, 'id')->toArray();
        } else {
            $suggestions = $customField->options->pluck('name', 'id')->all();
        }

        $field->suggestions($suggestions);

        return $this->configurator->configure($field, $customField);
    }
}
