<?php

namespace Relaticle\CustomFields\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Relaticle\CustomFields\Enums\CustomFieldSectionType;
use Relaticle\CustomFields\Services\EntityTypeService;

class CustomFieldSection extends Model
{
    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    protected function casts()
    {
        return [
            'type' => CustomFieldSectionType::class
        ];
    }

    public function fields(): HasMany
    {
        return $this->hasMany(CustomField::class);
    }

    public function scopeForEntityType(Builder $query, string $model)
    {
        return $query->where('entity_type', EntityTypeService::getEntityFromModel($model));
    }
}
