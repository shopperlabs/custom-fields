<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Contracts;

use Illuminate\Database\Eloquent\Model;
use Relaticle\CustomFields\Models\CustomField;

interface ValueResolvers
{
    public function resolve(Model $record, CustomField $customField, bool $exportable = false): mixed;
}
