<?php

namespace ManukMinasyan\FilamentCustomField\Migrations;

use ManukMinasyan\FilamentCustomField\Enums\CustomFieldType;
use ManukMinasyan\FilamentCustomField\Exceptions\CustomFieldAlreadyExists;
use ManukMinasyan\FilamentCustomField\Exceptions\CustomFieldDoesNotExist;
use ManukMinasyan\FilamentCustomField\Models\CustomField;

class CustomFieldsMigrator
{
    /**
     * @param  class-string  $model
     *
     * @throws CustomFieldAlreadyExists
     */
    public function add(string $model, CustomFieldType $type, string $name, string $code): void
    {
        if ($this->checkIfCustomFieldExists($model, $code)) {
            throw CustomFieldAlreadyExists::whenAdding($code);
        }

        $this->createCustomField(model: $model, type: $type, name: $name, code: $code);
    }

    /**
     * @throws CustomFieldDoesNotExist
     */
    public function delete(string $model, string $code): void
    {
        if (! $this->checkIfCustomFieldExists($model, $code)) {
            throw CustomFieldDoesNotExist::whenDeleting($code);
        }

        $this->deleteCustomField($model, $code);
    }

    protected function checkIfCustomFieldExists(string $model, string $code): bool
    {
        return CustomField::query()
            ->forEntity($model)
            ->where('code', $code)
            ->exists();
    }

    /**
     * @param  class-string  $model
     */
    protected function createCustomField(string $model, CustomFieldType $type, string $name, string $code): void
    {
        CustomField::query()->create([
            'name' => $name,
            'code' => $code,
            'type' => $type,
            'entity_type' => app($model)->getMorphClass(),
            'active' => true,
            'user_defined' => true,
        ]);
    }

    protected function deleteCustomField(string $model, string $code): void
    {
        CustomField::query()
            ->forEntity($model)
            ->where('code', $code)
            ->forceDelete();
    }
}
