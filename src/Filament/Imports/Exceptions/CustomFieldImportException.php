<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Imports\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown when there's an error with custom field imports.
 */
class CustomFieldImportException extends Exception
{
    /**
     * Additional context information about the exception.
     *
     * @var array<string, mixed>
     */
    private array $context = [];

    /**
     * Constructor with additional context support.
     *
     * @param  string  $message  The exception message
     * @param  array<string, mixed>  $context  Additional context information
     * @param  int  $code  The exception code
     * @param  Throwable|null  $previous  Previous exception
     */
    public function __construct(
        string $message = '',
        array $context = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get the additional context information.
     *
     * @return array<string, mixed> The context information
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
