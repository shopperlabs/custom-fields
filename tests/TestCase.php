<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Relaticle\CustomFields\CustomFieldsServiceProvider;



class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            CustomFieldsServiceProvider::class,
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,       // Only if you are requiring the admin panel.
            FormsServiceProvider::class,
            SupportServiceProvider::class,
            BladeIconsServiceProvider::class,     // Only if you are requiring the admin panel.
            BladeHeroiconsServiceProvider::class, // Only if you are requiring the admin panel.
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        // (include __DIR__.'/../database/migrations/your_migration_name.php.stub')->up();
    }
}