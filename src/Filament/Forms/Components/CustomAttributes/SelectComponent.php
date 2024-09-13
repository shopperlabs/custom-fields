<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentAttribute\Filament\Forms\Components\CustomAttributes;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Relations\Relation;
use ManukMinasyan\FilamentAttribute\Models\Attribute;

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

    protected function configureLookup(Select $select, $lookupType): Select
    {
        $lookupMorphedModelPath = Relation::getMorphedModel($lookupType);

        $lookupEntity = app($lookupMorphedModelPath);
        $resource = app(Filament::getModelResource($lookupMorphedModelPath));

        $recordTitleAttribute = $resource->getRecordTitleAttribute();
        $globalSearchableAttributes = $resource->getGloballySearchableAttributes();

        return $select
            ->getSearchResultsUsing(fn (string $search): array => $lookupEntity->query()
                ->whereAny($globalSearchableAttributes, 'like', "%{$search}%")
                ->limit(50)
                ->pluck($recordTitleAttribute, 'id')
                ->toArray())
            ->getOptionLabelUsing(fn ($value) => $lookupEntity::query()->find($value)->{$recordTitleAttribute})
            ->getOptionLabelsUsing(fn (array $values): array => $lookupEntity::query()
                ->whereIn('id', $values)
                ->pluck($recordTitleAttribute, 'id')
                ->toArray());
    }
}
