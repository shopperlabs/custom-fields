<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Services;

use Relaticle\CustomFields\Models\CustomField;

interface ValueResolverInterface
{
    public function resolve($record, CustomField $customField): string;
}
