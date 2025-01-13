<?php

namespace Relaticle\CustomFields\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Relaticle\CustomFields\Models\CustomField;

class CustomFieldSection extends Model
{
    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    public function fields(): HasMany
    {
        return $this->hasMany(CustomField::class);
    }

    public function scopeForEntityType(Builder $query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }
}
