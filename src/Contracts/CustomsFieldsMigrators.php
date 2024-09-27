<?php

namespace Relaticle\CustomFields\Contracts;

use Illuminate\Database\Eloquent\Model;
use Relaticle\CustomFields\Enums\CustomFieldType;

interface CustomsFieldsMigrators
{
    public function setTenantId(int|string|null $tenantId = null): void;

    public function find(string $model, string $code): ?CustomsFieldsMigrators;
    public function new(string $model, CustomFieldType $type, string $name, string $code, bool $active = true, bool $systemDefined = false): CustomsFieldsMigrators;
    public function options(array $options): CustomsFieldsMigrators;
    public function lookupType(string $model): CustomsFieldsMigrators;
    public function create(): void;
    public function update(array $data): void;
    public function delete(): void;
    public function forceDelete(): void;
    public function restore(): void;
}
