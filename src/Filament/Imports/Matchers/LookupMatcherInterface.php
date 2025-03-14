<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Imports\Matchers;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface for matching lookup values to database records.
 */
interface LookupMatcherInterface
{
    /**
     * Find a record that matches the given value.
     *
     * @param  mixed  $entityInstance  The entity model instance
     * @param  string  $titleAttribute  The attribute to match against
     * @param  string  $value  The value to match
     * @return Model|null The matched record or null if not found
     */
    public function find(mixed $entityInstance, string $titleAttribute, string $value): ?Model;
}
