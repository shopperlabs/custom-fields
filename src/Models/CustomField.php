<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Models;

use App\Models\CustomFieldSection;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Relaticle\CustomFields\Data\ValidationRuleData;
use Relaticle\CustomFields\Database\Factories\CustomFieldFactory;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Models\Concerns\Activable;
use Relaticle\CustomFields\Models\Scopes\SortOrderScope;
use Relaticle\CustomFields\Models\Scopes\TenantScope;
use Relaticle\CustomFields\Services\EntityTypeService;
use Spatie\LaravelData\DataCollection;

/**
 * @property string $name
 * @property string $code
 * @property CustomFieldType $type
 * @property string $entity_type
 * @property string $lookup_type
 * @property DataCollection<int, ValidationRuleData> $validation_rules
 * @property int $sort_order
 * @property bool $active
 * @property bool $system_defined
 */
#[ScopedBy([TenantScope::class, SortOrderScope::class])]
final class CustomField extends Model
{
    /** @use HasFactory<CustomFieldFactory> */
    use HasFactory;
    use SoftDeletes;
    use Activable;

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (!isset($this->table)) {
            $this->setTable(config('custom-fields.table_names.custom_fields'));
        }

        parent::__construct($attributes);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CustomFieldType::class,
            'validation_rules' => DataCollection::class . ':' . ValidationRuleData::class . ',default',
            'active' => 'boolean',
            'system_defined' => 'boolean',
        ];
    }

    /**
     * @param Builder<CustomField> $builder
     * @return Builder<CustomField>
     *
     * @noinspection PhpUnused
     */
    public function scopeForType(Builder $builder, CustomFieldType $type): Builder
    {
        return $builder->where('type', $type);
    }

    /**
     * @param Builder<CustomField> $builder
     * @return Builder<CustomField>
     *
     * @noinspection PhpUnused
     */
    public function scopeForEntity(Builder $builder, string $model): Builder
    {
        return $builder->where(
            'entity_type',
            EntityTypeService::getEntityFromModel($model)
        );
    }

    /**
     * @param Builder<CustomField> $builder
     * @return Builder<CustomField>
     *
     * @noinspection PhpUnused
     */
    public function scopeForMorphEntity(Builder $builder, string $entity): Builder
    {
        return $builder->where('entity_type', $entity);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CustomFieldSection::class);
    }

    /**
     * @return HasMany<CustomFieldValue>
     */
    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    /**
     * @return HasMany<CustomFieldOption>
     */
    public function options(): HasMany
    {
        return $this->hasMany(CustomFieldOption::class);
    }

    /**
     * Determine if the model instance is user defined.
     */
    public function isSystemDefined(): bool
    {
        return $this->system_defined === true;
    }

    protected $appends = ['col_span_class'];

    public function getColSpanClassAttribute()
    {
        return match ((int) $this->width) {
            25 => 'col-span-3',
            33 => 'col-span-4',
            50 => 'col-span-6',
            66 => 'col-span-8',
            75 => 'col-span-9',
            default => 'col-span-12',
        };
    }
}
