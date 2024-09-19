<?php

namespace ManukMinasyan\FilamentCustomField\Exceptions;

use Exception;

class CustomFieldDoesNotExist extends Exception
{
    public static function whenDeleting(string $code): self
    {
        return new self("Could not delete custom field `{$code}` because it does not exist");
    }
}
