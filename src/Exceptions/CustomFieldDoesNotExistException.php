<?php

namespace Relaticle\CustomFields\Exceptions;

use Exception;

class CustomFieldDoesNotExistException extends Exception
{
    public static function whenUpdating(string $code): self
    {
        return new self("Could not update custom field `{$code}` because it does not exist");
    }

    public static function whenDeleting(string $code): self
    {
        return new self("Could not delete custom field `{$code}` because it does not exist");
    }

    public static function whenActivating(string $code): self
    {
        return new self("Could not activate custom field `{$code}` because it does not exist");
    }

    public static function whenDeactivating(string $code): self
    {
        return new self("Could not deactivate custom field `{$code}` because it does not exist");
    }
}
