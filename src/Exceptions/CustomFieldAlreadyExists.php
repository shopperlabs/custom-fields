<?php

namespace ManukMinasyan\FilamentCustomField\Exceptions;

use Exception;

class CustomFieldAlreadyExists extends Exception
{
    public static function whenAdding(string $code): self
    {
        throw new self("Could not create custom field `{$code}` because it already exists");
    }
}
