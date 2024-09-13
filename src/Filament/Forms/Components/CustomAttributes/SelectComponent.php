<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentAttribute\Filament\Forms\Components\CustomAttributes;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Relations\Relation;
use ManukMinasyan\FilamentAttribute\Models\Attribute;
use Throwable;

final readonly class SelectComponent implements AttributeComponentInterface
{
    public function __construct(private CommonAttributeConfigurator $configurator) {}

    public function make(Attribute $attribute): Select
    {
        $field = Select::make("custom_attributes.{$attribute->code}")->searchable();

        if ($attribute->lookup_type) {
            $field = $this->configureLookup($field, $attribute->lookup_type);
        } else {
            $field->options($attribute->options->pluck('name', 'id')->all());
        }

        /** @var Select */
        return $this->configurator->configure($field, $attribute);
    }

    /**
     * @throws Throwable
     */
    protected function configureLookup(Select $select, $lookupType): Select
    {
        $lookupMorphedModelPath = Relation::getMorphedModel($lookupType);

        $lookupEntityInstance = app($lookupMorphedModelPath);
        $resourcePath = Filament::getModelResource($lookupMorphedModelPath);
        $resourceInstance = app($resourcePath);

        $recordTitleAttribute = $resourceInstance->getRecordTitleAttribute();
        $globalSearchableAttributes = $resourceInstance->getGloballySearchableAttributes();

        throw_if($recordTitleAttribute === null, new \Exception("The `{$resourcePath}` does not have a record title attribute."));

        return $select
            ->getSearchResultsUsing(fn (string $search): array => $lookupEntityInstance->query()
                ->whereAny($globalSearchableAttributes, 'like', "%{$search}%")
                ->limit(50)
                ->pluck($recordTitleAttribute, 'id')
                ->toArray())
            ->getOptionLabelUsing(fn ($value) => $lookupEntityInstance::query()->find($value)->{$recordTitleAttribute})
            ->getOptionLabelsUsing(fn (array $values): array => $lookupEntityInstance::query()
                ->whereIn('id', $values)
                ->pluck($recordTitleAttribute, 'id')
                ->toArray());
    }
}
