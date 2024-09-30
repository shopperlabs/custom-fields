<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Models\Scopes;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Relaticle\CustomFields\Support\Utils;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Utils::isTenantEnabled() && Filament::getTenant()) {
            $builder->where(
                config('custom-fields.column_names.tenant_foreign_key'),
                Filament::getTenant()?->id
            );
        }
    }
}
