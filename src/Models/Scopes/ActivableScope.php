<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActivableScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected $extensions = ['WithDeactivated', 'WithoutDeactivated'];


    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->getQualifiedActiveColumn(), true);
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<*>  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }

        $builder->onDelete(function (Builder $builder) {
            $column = $this->getDeletedAtColumn($builder);

            return $builder->update([
                $column => $builder->getModel()->freshTimestampString(),
            ]);
        });
    }

    /**
     * Add the with-trashed extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<*>  $builder
     * @return void
     */
    protected function addWithDeactivated(Builder $builder)
    {
        $builder->macro('withDeactivated', function (Builder $builder, $withDeactivated = true) {
            if (! $withDeactivated) {
                return $builder->withoutActivated();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-trashed extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<*>  $builder
     * @return void
     */
    protected function addWithoutDeactivated(Builder $builder)
    {
        $builder->macro('withoutDeactivated', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->whereNull(
                $model->getQualifiedDeletedAtColumn()
            );

            return $builder;
        });
    }
}
