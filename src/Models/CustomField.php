<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Models;

use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ManukMinasyan\FilamentCustomField\Data\ValidationRuleData;
use ManukMinasyan\FilamentCustomField\Database\Factories\AttributeFactory;
use ManukMinasyan\FilamentCustomField\Enums\CustomFieldType;
use ManukMinasyan\FilamentCustomField\Models\Scopes\SortOrderScope;
use Spatie\LaravelData\DataCollection;

/**
 * @property string $name
 * @property string $code
 * @property CustomFieldType $type
 * @property string $entity_type
 * @property string $lookup_type
 * @property DataCollection<int, ValidationRuleData> $validation_rules
 * @property int $sort_order
 */
#[ScopedBy([SortOrderScope::class])]
final class CustomField extends Model
{
    /** @use HasFactory<AttributeFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'type',
        'entity_type',
        'lookup_type',
        'validation_rules',
        'sort_order',
    ];

    public function __construct(array $customFields = [])
    {
        if (! isset($this->table)) {
            $this->setTable(config('custom-fields.table_names.custom_fields'));
        }

        parent::__construct($customFields);
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
            'validation_rules' => DataCollection::class.':'.ValidationRuleData::class,
        ];
    }

    /**
     * @param  Builder<CustomField>  $builder
     * @return Builder<CustomField>
     *
     * @noinspection PhpUnused
     */
    public function scopeForType(Builder $builder, CustomFieldType $type): Builder
    {
        return $builder->where('type', $type);
    }

    /**
     * @param  Builder<CustomField>  $builder
     * @return Builder<CustomField>
     *
     * @noinspection PhpUnused
     */
    public function scopeForEntity(Builder $builder, string $entity): Builder
    {
        return $builder->where('entity_type', $entity);
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
}
