<?php

namespace Relaticle\CustomFields\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Relaticle\CustomFields\CustomField
 */
class FilamentCustomField extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Relaticle\CustomFields\CustomField::class;
    }
}
