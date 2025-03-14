<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Imports\Matchers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Default implementation of the lookup matcher interface.
 *
 * Uses progressive matching strategies from most to least precise:
 * 1. Exact match
 * 2. Case-insensitive exact match
 * 3. Case-insensitive starts with match
 * 4. Case-insensitive contains match
 */
final class LookupMatcher implements LookupMatcherInterface
{
    /**
     * Constructor with dependency injection.
     */
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Find a record that matches the given value.
     *
     * @param  mixed  $entityInstance  The entity model instance
     * @param  string  $titleAttribute  The attribute to match against
     * @param  string  $value  The value to match
     * @return Model|null The matched record or null if not found
     */
    public function find(mixed $entityInstance, string $titleAttribute, string $value): ?Model
    {
        try {
            // 1. Exact match (most precise)
            $record = $entityInstance::query()
                ->where($titleAttribute, $value)
                ->first();

            if ($record) {
                return $record;
            }

            // 2. Case-insensitive exact match
            $record = $entityInstance::query()
                ->whereRaw(DB::raw("LOWER({$titleAttribute}) = ?"), [strtolower($value)])
                ->first();

            if ($record) {
                return $record;
            }

            // 3. Case-insensitive starts with match
            $record = $entityInstance::query()
                ->whereRaw(DB::raw("LOWER({$titleAttribute}) LIKE ?"), [strtolower($value).'%'])
                ->first();

            if ($record) {
                return $record;
            }

            // 4. Case-insensitive contains match (least precise)
            $record = $entityInstance::query()
                ->whereRaw(DB::raw("LOWER({$titleAttribute}) LIKE ?"), ['%'.strtolower($value).'%'])
                ->first();

            return $record;
        } catch (Throwable $e) {
            // Log the error but don't throw - we'll handle this gracefully by returning null
            $this->logger->warning('Error matching lookup value', [
                'entity' => get_class($entityInstance),
                'attribute' => $titleAttribute,
                'value' => $value,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
