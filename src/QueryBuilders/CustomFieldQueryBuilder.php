<?php

namespace Relaticle\CustomFields\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Services\EntityTypeService;

class CustomFieldQueryBuilder extends Builder
{
    /**
     * @param CustomFieldType $type
     * @return CustomFieldQueryBuilder
     *
     */
    public function forType(CustomFieldType $type): self
    {
        return $this->where('type', $type);
    }

    /**
     * @param string $model
     * @return CustomFieldQueryBuilder
     *
     */
    public function forEntity( string $model): self
    {
        return $this->where(
            'entity_type',
            EntityTypeService::getEntityFromModel($model)
        );
    }


    /**
     * @param string $entity
     * @return CustomFieldQueryBuilder
     *
     */
    public function forMorphEntity(string $entity): self
    {
        return $this->where('entity_type', $entity);
    }


    /**
     * Scope to filter non-encrypted fields including NULL settings
     *
     * @return CustomFieldQueryBuilder
     */
    public function nonEncrypted(): self
    {
        return $this->where(function($query) {
            $query->whereNull('settings')->orWhereJsonDoesntContain('settings->encrypted', true);
        });
    }

    /**
     * @return CustomFieldQueryBuilder
     */
    public function visibleInList(): self
    {
        return $this->where(function($query) {
            $query->whereNull('settings')->orWhereJsonDoesntContain('settings->visible_in_list', false);
        });
    }

    /**
     * @return CustomFieldQueryBuilder
     */
    public function visibleInView(): self
    {
        return $this->where(function($query) {
            $query->whereNull('settings')->orWhereJsonDoesntContain('settings->visible_in_view', false);
        });
    }

    /**
     * @return CustomFieldQueryBuilder
     */
    public function searchable(): self
    {
        return $this->whereJsonContains('settings->searchable', true);
    }
}
