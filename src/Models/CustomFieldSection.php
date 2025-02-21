<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Relaticle\CustomFields\Data\CustomFieldSectionSettingsData;
use Relaticle\CustomFields\Enums\CustomFieldSectionType;
use Relaticle\CustomFields\Models\Concerns\Activable;
use Relaticle\CustomFields\Models\Scopes\SortOrderScope;
use Relaticle\CustomFields\Models\Scopes\TenantScope;
use Relaticle\CustomFields\Observers\CustomFieldSectionObserver;
use Relaticle\CustomFields\Services\EntityTypeService;

/**
 * @property string $name
 * @property string $code
 * @property CustomFieldSectionType $type
 * @property string $entity_type
 * @property string $lookup_type
 * @property CustomFieldSectionSettingsData $settings
 * @property int $sort_order
 * @property bool $active
 * @property bool $system_defined
 */
#[ScopedBy([TenantScope::class, SortOrderScope::class])]
#[ObservedBy(CustomFieldSectionObserver::class)]
class CustomFieldSection extends Model
{
    use Activable;

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => CustomFieldSectionType::class,
            'settings' => CustomFieldSectionSettingsData::class . ':default',
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

    /**
     * Determine if the model instance is user defined.
     */
    public function isSystemDefined(): bool
    {
        return $this->system_defined === true;
    }
}
