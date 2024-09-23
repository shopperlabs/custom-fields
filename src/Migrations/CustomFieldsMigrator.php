<?php

namespace Relaticle\CustomFields\Migrations;

use Illuminate\Support\Facades\DB;
use Relaticle\CustomFields\Data\CustomFieldData;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Exceptions\CustomFieldAlreadyExists;
use Relaticle\CustomFields\Exceptions\CustomFieldDoesNotExist;
use Relaticle\CustomFields\Models\CustomField;

class CustomFieldsMigrator
{
    private CustomFieldData $field;

    /**
     * @param  class-string  $model
     */
    public function new(string $model, CustomFieldType $type, string $name, string $code, bool $active = true, bool $userDefined = false): CustomFieldsMigrator
    {
        $this->field = CustomFieldData::from([
            'entity_type' => app($model)->getMorphClass(),
            'type' => $type,
            'name' => $name,
            'code' => $code,
            'active' => $active,
            'user_defined' => $userDefined,
        ]);

        return $this;
    }

    public function options(array $options): CustomFieldsMigrator
    {
        $this->field->options = $options;

        return $this;
    }

    /**
     * @throws CustomFieldAlreadyExists
     */
    public function create(): void
    {
        try {
            DB::beginTransaction();

            if ($this->checkIfCustomFieldExists($this->field->entityType, $this->field->code)) {
                throw CustomFieldAlreadyExists::whenAdding($this->field->code);
            }

            $customField = $this->createCustomField();

            if (CustomFieldType::optionables()->contains('value', $this->field->type->value) && ! empty($this->field->options)) {
                $this->createOptions($customField, $this->field->options);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function createOptions(CustomField $customField, array $options): void
    {
        $customField->options()->createMany(collect($options)->map(fn ($value) => ['name' => $value])->toArray());
    }

    /**
     * @throws CustomFieldDoesNotExist
     */
    public function delete(string $model, string $code): void
    {
        $model = app($model)->getMorphClass();

        if (! $this->checkIfCustomFieldExists($model, $code)) {
            throw CustomFieldDoesNotExist::whenDeleting($code);
        }

        $this->deleteCustomField($model, $code);
    }

    protected function checkIfCustomFieldExists(string $model, string $code): bool
    {
        return CustomField::query()
            ->forMorphEntity($model)
            ->where('code', $code)
            ->exists();
    }

    protected function createCustomField(): CustomField
    {
        return CustomField::query()->create($this->field->toArray());
    }

    protected function deleteCustomField(string $model, string $code): void
    {
        CustomField::query()
            ->forMorphEntity($model)
            ->where('code', $code)
            ->forceDelete();
    }
}
