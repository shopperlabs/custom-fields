<?php

namespace ManukMinasyan\FilamentCustomField\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ManukMinasyan\FilamentCustomField\FilamentCustomField
 */
class FilamentCustomField extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \ManukMinasyan\FilamentCustomField\FilamentCustomField::class;
    }
}
