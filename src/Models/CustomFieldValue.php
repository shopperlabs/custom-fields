<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Models;

use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
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

    public $timestamps = false;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (!isset($this->table)) {
            $this->setTable(config('custom-fields.table_names.custom_field_values'));
        }

        parent::__construct($attributes);
    }

    public static function getValueColumn(): string
    {
        return 'text_value';
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

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        $column = $this->getValueColumn();

        $value = $this->$column;

        return match ($this->customField->type) {
            CustomFieldType::DATE => $value instanceof Carbon ? $value->toDateString() : $value,
            CustomFieldType::DATE_TIME => $value instanceof Carbon ? $value->toDateTimeString() : $value,
            CustomFieldType::TOGGLE, CustomFieldType::CHECKBOX => (bool)$value,
            CustomFieldType::CHECKBOX_LIST,
            CustomFieldType::MULTI_SELECT,
            CustomFieldType::TAGS_INPUT,
            CustomFieldType::TOGGLE_BUTTONS, => $value ? json_decode($value) : [],
            default => $value,
        };
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function setValue(mixed $value): void
    {
        $column = $this->getValueColumn();

        $value = gettype($value) === 'array' ? json_encode($value) : $value;

        $this->$column = $value;
    }
}
