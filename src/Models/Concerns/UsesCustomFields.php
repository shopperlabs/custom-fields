<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use ManukMinasyan\FilamentCustomField\Models\CustomField;
use ManukMinasyan\FilamentCustomField\Models\CustomFieldValue;
use ManukMinasyan\FilamentCustomField\Models\Contracts\HasCustomFields;

/**
 * @see HasCustomFields
 */
trait UsesCustomFields
{
    public function __construct($customFields = [])
    {
        // Ensure custom fields are included in a fillable array
        $this->fillable = array_merge(['custom_fields'], $this->fillable);
        parent::__construct($customFields);
    }

    /**
     * @var array<int, array<string, mixed>>
     */
    protected static array $tempCustomFields = [];

    protected static function bootUsesCustomFields(): void
    {
        static::saving(function (Model $model): void {
            $model->handleCustomFields();
        });

        static::saved(function (Model $model): void {
            $model->saveCustomFieldsFromTemp();
        });
    }

    /**
     * Handle the custom fields before saving the model.
     */
    protected function handleCustomFields(): void
    {
        if (isset($this->custom_fields) && is_array($this->custom_fields)) {
            self::$tempCustomFields[spl_object_id($this)] = $this->custom_fields;
            unset($this->custom_fields);
        }
    }

    /**
     * Save custom fields from temporary storage after the model is created/updated.
     */
    protected function saveCustomFieldsFromTemp(): void
    {
        $objectId = spl_object_id($this);

        if (isset(self::$tempCustomFields[$objectId]) && method_exists($this, 'saveCustomFields')) {
            $this->saveCustomFields(self::$tempCustomFields[$objectId]);
            unset(self::$tempCustomFields[$objectId]);
        }
    }

    /**
     * @return Builder<CustomField>
     */
    public function customFields(): Builder
    {
        return CustomField::query()->forEntity($this->getMorphClass());
    }

    /**
     * @return MorphMany<CustomFieldValue>
     */
    public function customFieldValues(): MorphMany
    {
        return $this->morphMany(CustomFieldValue::class, 'entity');
    }

    public function getCustomFieldValue(string $code): mixed
    {
        $customField = $this->customFields()->where('code', $code)->first();

        if (! $customField) {
            return null;
        }

        $customFieldValue = $customField->values()->first();

        $customFieldValue = $customFieldValue ? $customFieldValue->getValue() : null;

        return $customFieldValue instanceof Collection ? $customFieldValue->toArray() : $customFieldValue;
    }

    public function saveCustomFieldValue(string $code, mixed $value): void
    {
        $customField = $this->customFields()->where('code', $code)->firstOrFail();

        $customFieldValue = $this->customFieldValues()->firstOrNew(['custom_field_id' => $customField->id]);

        $customFieldValue->setValue($value);
        $customFieldValue->save();
    }

    /**
     * @param  array<string, mixed>  $customFields
     */
    public function saveCustomFields(array $customFields): void
    {
        $this->customFields()->each(function (CustomField $customField) use ($customFields): void {
            $value = $customFields[$customField->code] ?? null;
            $this->saveCustomFieldValue($customField->code, $value);
        });
    }
}
