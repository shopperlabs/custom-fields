<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Services\ValueResolver;

use Illuminate\Database\Eloquent\Model;
use Relaticle\CustomFields\Contracts\ValueResolvers;
use Relaticle\CustomFields\Models\CustomField;

readonly class ValueResolver implements ValueResolvers
{
    /**
     * @param LookupMultiValueResolver $multiValueResolver
     * @param LookupSingleValueResolver $singleValueResolver
     */
    public function __construct(
        private LookupMultiValueResolver  $multiValueResolver,
        private LookupSingleValueResolver $singleValueResolver
    )
    {
    }

    /**
     * @param Model $record
     * @param CustomField $customField
     * @return mixed
     */
    public function resolve(Model $record, CustomField $customField): string
    {
        if (!$customField->type->isOptionable()) {
            return (string)$record->getCustomFieldValue($customField);
        }

        if ($customField->type->hasMultipleValues()) {
            return $this->multiValueResolver->resolve($record, $customField);
        }

        return $this->singleValueResolver->resolve($record, $customField);
    }
}
