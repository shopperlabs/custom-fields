<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentAttribute\Models;

use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ManukMinasyan\FilamentAttribute\Data\ValidationRuleData;
use ManukMinasyan\FilamentAttribute\Database\Factories\AttributeFactory;
use ManukMinasyan\FilamentAttribute\Enums\AttributeType;
use ManukMinasyan\FilamentAttribute\Models\Scopes\SortOrderScope;
use Spatie\LaravelData\DataCollection;

/**
 * @property AttributeType $type
 * @property Model $entity_type
 * @property Model|null $lookup_type
 */
#[ScopedBy([SortOrderScope::class])]
final class Attribute extends Model
{
    /** @use HasFactory<AttributeFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'entity_type',
        'type',
        'lookup_type',
        'name',
        'code',
        'validation_rules',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AttributeType::class,
            'validation_rules' => DataCollection::class.':'.ValidationRuleData::class,
        ];
    }

    /**
     * @param  Builder<Attribute>  $builder
     * @return Builder<Attribute>
     *
     * @noinspection PhpUnused
     */
    public function scopeForType(Builder $builder, AttributeType $type): Builder
    {
        return $builder->where('type', $type);
    }

    /**
     * @param  Builder<Attribute>  $builder
     * @return Builder<Attribute>
     *
     * @noinspection PhpUnused
     */
    public function scopeForEntity(Builder $builder, string $entity): Builder
    {
        return $builder->where('entity_type', $entity);
    }

    /**
     * @return HasMany<AttributeValue>
     */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    /**
     * @return HasMany<AttributeOption>
     */
    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class);
    }
}
