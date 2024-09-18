<?php

namespace ManukMinasyan\FilamentCustomField;

use Filament\Contracts\Plugin;
use Filament\Panel;
use ManukMinasyan\FilamentCustomField\Filament\Resources\CustomFieldResource;

class FilamentCustomFieldPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-custom-fields';
    }

    public function register(Panel $panel): void
    {

        $panel
            ->resources([
                CustomFieldResource::class,
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
