<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentAttribute\Models;

use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ManukMinasyan\FilamentAttribute\Database\Factories\AttributeOptionFactory;
use ManukMinasyan\FilamentAttribute\Models\Scopes\SortOrderScope;

#[ScopedBy([SortOrderScope::class])]
final class AttributeOption extends Model
{
    /** @use HasFactory<AttributeOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order'
    ];

    /**
     * @return BelongsTo<Attribute, AttributeOption>
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
