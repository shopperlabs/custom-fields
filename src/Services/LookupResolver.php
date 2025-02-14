<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Services;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Relaticle\CustomFields\Exceptions\MissingRecordTitleAttributeException;
use Relaticle\CustomFields\Models\CustomField;
use Throwable;

final readonly class LookupResolver
{
    /**
     * Resolve lookup values based on the custom field configuration.
     *
     * @throws Throwable
     */
    public function resolveLookupValues(array $values, CustomField $customField): Collection
    {
        if (!isset($customField->lookup_type)) {
            return $customField->options->whereIn('id', $values)->pluck('name');
        }

        [$lookupInstance, $recordTitleAttribute] = $this->getLookupAttributes($customField->lookup_type);
        return $lookupInstance->whereIn('id', $values)->pluck($recordTitleAttribute);
    }

    /**
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
