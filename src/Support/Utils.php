<?php

namespace Relaticle\CustomFields\Support;

use Filament\Facades\Filament;

class Utils
{
    public static function getResourceCluster(): ?string
    {
        return config('filament-shield.shield_resource.cluster', null);
    }

    public static function getResourceSlug(): string
    {
        return (string) config('custom-fields.custom_fields_resource.slug');
    }

    public static function isResourceNavigationRegistered(): bool
    {
        return config('custom-fields.custom_fields_resource.should_register_navigation', true);
    }

    public static function getResourceNavigationSort(): ?int
    {
        return config('custom-fields.custom_fields_resource.navigation_sort');
    }

    public static function isResourceNavigationBadgeEnabled(): bool
    {
        return config('custom-fields.custom_fields_resource.navigation_badge', false);
    }

    public static function isScopedToTenant(): bool
    {
        return config('custom-fields.custom_fields_resource.is_scoped_to_tenant', true);
    }

    public static function isResourceNavigationGroupEnabled(): bool
    {
        return config('custom-fields.custom_fields_resource.navigation_group', true);
    }

    public static function isResourceGloballySearchable(): bool
    {
        return config('custom-fields.custom_fields_resource.is_globally_searchable', false);
    }

    public static function isTenantEnabled(): bool
    {
        return config('custom-fields.tenant_aware', false);
    }
}
