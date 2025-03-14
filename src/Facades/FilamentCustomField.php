<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Facades;

use Illuminate\Support\Facades\Facade;
use Relaticle\CustomFields\CustomFields;

/**
 * @see \Relaticle\CustomFields\CustomField
 */
class FilamentCustomField extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CustomFields::customFieldModel();
    }
}
