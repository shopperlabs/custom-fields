<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Relaticle\CustomFields\CustomFieldsServiceProvider;
use Relaticle\CustomFields\Data\CustomFieldSettingsData;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Relaticle\\CustomFields\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            CustomFieldsServiceProvider::class,
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            SupportServiceProvider::class,
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('custom-fields.table_names.custom_field_sections', 'custom_field_sections');
        config()->set('custom-fields.table_names.custom_fields', 'custom_fields');
        config()->set('custom-fields.table_names.custom_field_values', 'custom_field_values');
        config()->set('custom-fields.table_names.custom_field_options', 'custom_field_options');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function createTestModelTable(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Create CustomField settings data properly formatted for the model.
     * 
     * @param array<string, mixed> $overrides
     * @return CustomFieldSettingsData
     */
    protected function createCustomFieldSettings(array $overrides = []): CustomFieldSettingsData
    {
        return new CustomFieldSettingsData(
            visible_in_list: $overrides['visible_in_list'] ?? true,
            list_toggleable_hidden: $overrides['list_toggleable_hidden'] ?? null,
            visible_in_view: $overrides['visible_in_view'] ?? true,
            searchable: $overrides['searchable'] ?? false,
            encrypted: $overrides['encrypted'] ?? false,
        );
    }

    /**
     * Create a basic CustomField for testing without settings issues.
     * 
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    protected function createCustomFieldData(array $attributes = []): array
    {
        $data = array_merge([
            'name' => 'Test Field',
            'code' => 'test_field',
            'type' => \Relaticle\CustomFields\Enums\CustomFieldType::TEXT,
            'entity_type' => 'App\\Models\\User',
        ], $attributes);

        // If settings are provided, convert them to the proper format
        if (isset($data['settings']) && is_array($data['settings'])) {
            $data['settings'] = $this->createCustomFieldSettings($data['settings']);
        }

        return $data;
    }
}