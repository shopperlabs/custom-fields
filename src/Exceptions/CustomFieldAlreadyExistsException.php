<?php

namespace Relaticle\CustomFields\Exceptions;

use Exception;

class CustomFieldAlreadyExistsException extends Exception
{
    public static function whenAdding(string $code): self
    {
        throw new self("Could not create custom field `{$code}` because it already exists");
    }
}
