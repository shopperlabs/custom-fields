<?php

namespace Relaticle\CustomFields\Migrations;

use Illuminate\Support\Facades\DB;
use Relaticle\CustomFields\Contracts\CustomsFieldsMigrators;
use Relaticle\CustomFields\Data\CustomFieldData;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Exceptions\CustomFieldAlreadyExistsException;
use Relaticle\CustomFields\Exceptions\CustomFieldDoesNotExistException;
use Relaticle\CustomFields\Exceptions\FieldTypeNotOptionableException;
use Relaticle\CustomFields\Models\CustomField;

class CustomFieldsMigrator implements CustomsFieldsMigrators
{
    private CustomFieldData $customFieldData;

    private ?CustomField $customField;

    public function find(string $model, string $code): CustomFieldsMigrator
    {
        $this->customField = CustomField::query()
            ->withTrashed()
            ->forMorphEntity(app($model)->getMorphClass())
            ->where('code', $code)
            ->firstOrFail();

        $this->customFieldData = CustomFieldData::from($this->customField);

        return $this;
    }

    /**
     * @param class-string $model
     */
    public function new(string $model, CustomFieldType $type, string $name, string $code, bool $active = true, bool $userDefined = false): CustomFieldsMigrator
    {
        $this->customFieldData = CustomFieldData::from([
            'entity_type' => app($model)->getMorphClass(),
            'type' => $type,
            'name' => $name,
            'code' => $code,
            'active' => $active,
            'user_defined' => $userDefined,
        ]);

        return $this;
    }

    /**
     * @throws FieldTypeNotOptionableException
     */
    public function options(array $options): CustomFieldsMigrator
    {
        if (!$this->isCustomFieldTypeOptionable()) {
            throw new FieldTypeNotOptionableException();
        }

        $this->customFieldData->options = $options;

        return $this;
    }

    /**
     * @throws FieldTypeNotOptionableException
     */
    public function lookupType(string $model): CustomFieldsMigrator
    {
        if (!$this->isCustomFieldTypeOptionable()) {
            throw new FieldTypeNotOptionableException();
        }

        $this->customFieldData->lookupType = app($model)->getMorphClass();

        return $this;
    }

    /**
     * @throws CustomFieldAlreadyExistsException
     * @throws \Exception
     */
    public function create(): void
    {
        if ($this->isCustomFieldExists($this->customFieldData->entityType, $this->customFieldData->code)) {
            throw CustomFieldAlreadyExistsException::whenAdding($this->customFieldData->code);
        }

        try {
            DB::beginTransaction();

            $customField = CustomField::query()->create($this->customFieldData->toArray());

            if ($this->isCustomFieldTypeOptionable() && !empty($this->customFieldData->options)) {
                $this->createOptions($customField, $this->customFieldData->options);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param array $data
     * @return void
     * @throws CustomFieldDoesNotExistException
     */
    public function update(array $data): void
    {
        if (!$this->customField->exists) {
            throw CustomFieldDoesNotExistException::whenUpdating($this->customFieldData->code);
        }

        try {
            DB::beginTransaction();

            collect($data)->each(fn($value, $key) => $this->customFieldData->$key = $value);

            $this->customField->update($this->customFieldData->toArray());

            if ($this->isCustomFieldTypeOptionable() && !empty($this->customFieldData->options)) {
                $this->customField->options()->delete();
                $this->createOptions($this->customField, $this->customFieldData->options);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }


    /**
     * @throws CustomFieldDoesNotExistException
     */
    public function delete(): void
    {
        if (!$this->customField) {
            throw CustomFieldDoesNotExistException::whenDeleting($this->customField->code);
        }

        $this->customField->delete();
    }

    /**
     * @throws CustomFieldDoesNotExistException
     */
    public function forceDelete(): void
    {
        if (!$this->customField) {
            throw CustomFieldDoesNotExistException::whenDeleting($this->customField->code);
        }

        $this->customField->forceDelete();
    }

    /**
     * @throws CustomFieldDoesNotExistException
     */
    public function restore(): void
    {
        if (!$this->customField) {
            throw CustomFieldDoesNotExistException::whenRestoring($this->customField->code);
        }

        if (!$this->customField->trashed()) {
            return;
        }

        $this->customField->restore();
    }

    protected function createOptions(CustomField $customField, array $options): void
    {
        $customField->options()->createMany(collect($options)->map(fn($value) => ['name' => $value])->toArray());
    }

    /**
     * @param string $model
     * @param string $code
     * @return bool
     */
    protected function isCustomFieldExists(string $model, string $code): bool
    {
        return CustomField::query()
            ->forMorphEntity($model)
            ->where('code', $code)
            ->exists();
    }

    protected function isCustomFieldTypeOptionable(): bool
    {
        return CustomFieldType::optionables()->contains('value', $this->customFieldData->type->value);
    }
}
