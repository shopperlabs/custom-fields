<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Models\CustomFieldValue;

interface HasCustomFields
{
    /**
     * @return Builder<CustomField>
     */
    public function customFields(): Builder;

    /**
     * @return MorphMany<CustomFieldValue>
     */
    public function customFieldValues(): MorphMany;

    public function getCustomFieldValue(CustomField $customField): mixed;

    public function saveCustomFieldValue(string $code, mixed $value): void;

    /**
     * @param  array<string, mixed>  $customFields
     */
    public function saveCustomFields(array $customFields): void;
}
