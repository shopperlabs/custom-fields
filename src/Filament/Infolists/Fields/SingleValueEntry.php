<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Infolists\Fields;

use Filament\Facades\Filament;
use Filament\Infolists\Components\Entry;
use Filament\Infolists\Components\TextEntry as BaseTextEntry;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Relaticle\CustomFields\Exceptions\MissingRecordTitleAttributeException;
use Relaticle\CustomFields\Filament\Infolists\FieldInfolistsComponentInterface;
use Relaticle\CustomFields\Filament\Infolists\FieldInfolistsConfigurator;
use Relaticle\CustomFields\Models\CustomField;
use Throwable;

final readonly class SingleValueEntry implements FieldInfolistsComponentInterface
{
    public function __construct(private FieldInfolistsConfigurator $configurator)
    {
    }

    public function make(CustomField $customField): Entry
    {
        return $this->configurator->configure(
            BaseTextEntry::make("custom_fields.{$customField->code}"),
            $customField
        )
            ->getStateUsing(fn($record) => $this->getSelectColumnValue($record, $customField));
    }

    /**
     * Get the value for a select column.
     *
     * @throws Throwable
     */
    private function getSelectColumnValue($record, CustomField $customField): string
    {
        $value = $record->getCustomFieldValue($customField->code);
        $lookupValue = $this->resolveLookupValues([$value], $customField)->first();

        return (string)$lookupValue;
    }

    /**
     * Resolve multiple lookup options based on the custom field configuration.
     *
     * @throws Throwable
     */
    private function resolveLookupValues(array $values, CustomField $customField): Collection
    {
        if (!isset($customField->lookup_type)) {
            return $customField->options->whereIn('id', $values)->pluck('name');
        }

        [$lookupInstance, $recordTitleAttribute] = $this->getLookupAttributes($customField->lookup_type);

        return $lookupInstance->whereIn('id', $values)->pluck($recordTitleAttribute);
    }

    /**
     * Get the lookup instance and record title custom field based on the custom field configuration.
     *
     * @throws Throwable
     */
    private function getLookupAttributes(string $lookupType): array
    {
        $lookupModelPath = Relation::getMorphedModel($lookupType) ?: $lookupType;
        $lookupInstance = app($lookupModelPath);

        $resourcePath = Filament::getModelResource($lookupModelPath);
        $resourceInstance = app($resourcePath);
        $recordTitleAttribute = $resourceInstance->getRecordTitleAttribute();

        throw_if(
            $recordTitleAttribute === null,
            new MissingRecordTitleAttributeException("The `{$resourcePath}` does not have a record title custom attribute.")
        );

        return [$lookupInstance, $recordTitleAttribute];
    }
}
