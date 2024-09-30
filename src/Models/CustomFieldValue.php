<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Models;

use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Relaticle\CustomFields\Database\Factories\CustomFieldValueFactory;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Models\Scopes\TenantScope;

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
#[ScopedBy([TenantScope::class])]
final class CustomFieldValue extends Model
{
    /** @use HasFactory<CustomFieldValueFactory> */
    use HasFactory;
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'custom_field_id',
        'text_value',
        'string_value',
        'integer_value',
        'float_value',
        'json_value',
        'boolean_value',
        'date_value',
        'datetime_value',
    ];

    /**
     * @var array<string, string>
     */
    public static array $valueColumns = [
        CustomFieldType::TEXT->value => 'text_value',
        CustomFieldType::NUMBER->value => 'integer_value',
        CustomFieldType::CHECKBOX->value => 'boolean_value',
        CustomFieldType::CHECKBOX_LIST->value => 'json_value',
        CustomFieldType::TEXTAREA->value => 'text_value',
        CustomFieldType::TOGGLE_BUTTONS->value => 'json_value',
        CustomFieldType::TAGS_INPUT->value => 'json_value',
        CustomFieldType::LINK->value => 'string_value',
        CustomFieldType::RICH_EDITOR->value => 'text_value',
        CustomFieldType::MARKDOWN_EDITOR->value => 'text_value',
        CustomFieldType::RADIO->value => 'integer_value',
        CustomFieldType::SELECT->value => 'integer_value',
        CustomFieldType::COLOR_PICKER->value => 'string_value',
        CustomFieldType::CURRENCY->value => 'float_value',
        CustomFieldType::MULTI_SELECT->value => 'json_value',
        CustomFieldType::TOGGLE->value => 'boolean_value',
        CustomFieldType::DATE->value => 'date_value',
        CustomFieldType::DATE_TIME->value => 'datetime_value',
    ];


    public function __construct(array $attributes = [])
    {
        if (!isset($this->table)) {
            $this->setTable(config('custom-fields.table_names.custom_field_values'));
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
            'string_value' => 'string',
            'text_value' => 'string',
            'integer_value' => 'integer',
            'float_value' => 'float',
            'json_value' => 'collection',
            'boolean_value' => 'boolean',
            'date_value' => 'date',
            'datetime_value' => 'datetime',
        ];

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
