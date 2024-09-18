<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use ManukMinasyan\FilamentCustomField\Enums\CustomFieldType;

/**
 * @property CustomField $customField
 * @property ?string $text_value
 * @property ?int $integer_value
 * @property ?float $float_value
 * @property ?Collection $json_value
 * @property ?bool $boolean_value
 * @property ?Carbon $date_value
 * @property ?Carbon $datetime_value
 */
final class CustomFieldValue extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'custom_field_id',
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
    public static array $valueColumns = [
        CustomFieldType::TEXT->value => 'text_value',
        CustomFieldType::TEXTAREA->value => 'text_value',
        CustomFieldType::SELECT->value => 'integer_value',
        CustomFieldType::PRICE->value => 'float_value',
        CustomFieldType::MULTISELECT->value => 'json_value',
        CustomFieldType::TOGGLE->value => 'boolean_value',
        CustomFieldType::DATE->value => 'date_value',
        CustomFieldType::DATETIME->value => 'datetime_value',
    ];

    public function __construct(array $customFields = [])
    {
        if (! isset($this->table)) {
            $this->setTable(config('custom-fields.table_names.custom_field_values'));
        }

        parent::__construct($customFields);
    }

    /**
     * @return BelongsTo<CustomField, CustomFieldValue>
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class);
    }

    /**
     * @return MorphTo<Model, CustomFieldValue>
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
        $type = $this->customField->type->value;

        return self::$valueColumns[$type]
            ?? throw new \InvalidArgumentException("Unsupported custom field type: {$type}");
    }
}
