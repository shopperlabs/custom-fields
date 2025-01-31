<?php

namespace Relaticle\CustomFields;

use Filament\Contracts\Plugin;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Relaticle\CustomFields\Filament\Pages\CustomFields;
use Relaticle\CustomFields\Http\Middleware\ApplyTenantScopes;

class CustomFieldsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'custom-fields';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                CustomFields::class
            ])
            ->discoverPages(in: __DIR__.'/Filament/Pages', for: 'ManukMinasyan\\FilamentCustomField\\Filament\\Pages');
    }

    public function boot(Panel $panel): void
    {
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
