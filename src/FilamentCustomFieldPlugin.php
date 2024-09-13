<?php

namespace ManukMinasyan\FilamentCustomField;

use Filament\Contracts\Plugin;
use Filament\Panel;
use ManukMinasyan\FilamentCustomField\Filament\Resources\AttributeResource;
use Filament\Navigation\NavigationItem;

class FilamentCustomFieldPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-attribute';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            AttributeResource::class,
        ]);
    }

    public function boot(Panel $panel): void {}

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
