<?php

namespace ManukMinasyan\FilamentCustomField\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ManukMinasyan\FilamentAttribute\FilamentCustomField
 */
class FilamentAttribute extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \ManukMinasyan\FilamentAttribute\FilamentCustomField::class;
    }
}
