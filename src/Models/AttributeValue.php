<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use ManukMinasyan\FilamentCustomField\Enums\AttributeType;

/**
 * @property Attribute $attribute
 * @property ?string $text_value
 * @property ?int $integer_value
 * @property ?float $float_value
 * @property ?Collection $json_value
 * @property ?bool $boolean_value
 * @property ?Carbon $date_value
 * @property ?Carbon $datetime_value
 */
final class AttributeValue extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'attribute_id',
        'text_value',
        'integer_value',
        'float_value',
        'json_value',
        'boolean_value',
        'date_value',
        'datetime_value',
    ];

    protected $casts = [
        'text_value' => 'string',
        'integer_value' => 'integer',
        'float_value' => 'float',
        'json_value' => 'collection',
        'boolean_value' => 'boolean',
        'date_value' => 'date',
        'datetime_value' => 'datetime',
    ];

    /**
     * @var array<string, string>
     */
    public static array $attributeTypeFields = [
        AttributeType::TEXT->value => 'text_value',
        AttributeType::TEXTAREA->value => 'text_value',
        AttributeType::SELECT->value => 'integer_value',
        AttributeType::PRICE->value => 'float_value',
        AttributeType::MULTISELECT->value => 'json_value',
        AttributeType::TOGGLE->value => 'boolean_value',
        AttributeType::DATE->value => 'date_value',
        AttributeType::DATETIME->value => 'datetime_value',
    ];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->table)) {
            $this->setTable(config('custom-fields.table_names.attribute_values'));
        }

        parent::__construct($attributes);
    }

    /**
     * @return BelongsTo<Attribute, AttributeValue>
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * @return MorphTo<Model, AttributeValue>
     */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function getValue(): mixed
    {
        $column = $this->getValueColumn();

        return $this->$column;
    }

    public function setValue(mixed $value): void
    {
        $column = $this->getValueColumn();
        $this->$column = $value;
    }

    public function getValueColumn(): string
    {
        $attributeType = $this->attribute->type->value;

        return self::$attributeTypeFields[$attributeType]
            ?? throw new \InvalidArgumentException("Unsupported attribute type: {$attributeType}");
    }
}
