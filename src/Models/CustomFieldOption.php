<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Models;

use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ManukMinasyan\FilamentCustomField\Database\Factories\AttributeOptionFactory;
use ManukMinasyan\FilamentCustomField\Models\Scopes\SortOrderScope;

#[ScopedBy([SortOrderScope::class])]
final class CustomFieldOption extends Model
{
    /** @use HasFactory<AttributeOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order'
    ];

    public function __construct(array $customFields = [])
    {
        if (!isset($this->table)) {
            $this->setTable(config('custom-fields.table_names.custom_field_options'));
        }

        parent::__construct($customFields);
    }

    /**
     * @return BelongsTo<CustomField, CustomFieldOption>
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class);
    }
}
