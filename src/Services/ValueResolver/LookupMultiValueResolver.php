<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Services\ValueResolver;

use Relaticle\CustomFields\Contracts\ValueResolvers;
use Relaticle\CustomFields\Models\CustomField;

final readonly class LookupMultiValueResolver implements ValueResolvers
{
    public function __construct(private LookupResolver $lookupResolver)
    {
    }

    public function resolve($record, CustomField $customField): string
    {
        $value = $record->getCustomFieldValue($customField->code) ?? [];
        $lookupValues = $this->lookupResolver->resolveLookupValues($value, $customField);

        return $lookupValues->isNotEmpty() ? $lookupValues->implode(', ') : '';
    }
}
