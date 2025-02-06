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
use Relaticle\CustomFields\Models\Scopes\TenantScope;
use Relaticle\CustomFields\Support\FieldTypeUtils;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
     * Set the value of the date_value attribute.
     */
    protected function dateValue(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                return $value ? Carbon::createFromFormat(FieldTypeUtils::getDateFormat(), $value) : null;
            },
        );
    }

    /**
     * Set the value of the datetime_value attribute.
     */
    protected function datetimeValue(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                return $value ? Carbon::createFromFormat(FieldTypeUtils::getDateTimeFormat(), $value) : null;
            },
        );
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
}
