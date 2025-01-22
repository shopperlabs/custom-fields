<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Relaticle\CustomFields\Enums\CustomFieldSectionType;
use Relaticle\CustomFields\Enums\CustomFieldWidth;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Models\CustomFieldSection;
use Relaticle\CustomFields\Support\Utils;

class UpgradeCustomFieldsToV2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom-fields:upgrade-v2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upgrade the Custom Fields Filament Plugin to version 2.';

    public function handle(): void
    {
        $this->info('Starting the upgrade of Custom Fields Filament Plugin to version 2...');

        // 1. Update Database Schema
        $this->updateDatabaseSchema();

        // 2. Update Existing Data
        $this->updateExistingData();

        $this->info('Upgrade completed successfully.');
    }

    /**
     * Update the database schema without using migrations.
     */
    private function updateDatabaseSchema(): void
    {
        $this->info('Updating database schema...');

        // Create 'custom_field_sections' table if it doesn't exist
        if (!Schema::hasTable('custom_field_sections')) {
            Schema::create(config('custom-fields.table_names.custom_field_sections'), function (Blueprint $table): void {
                $uniqueColumns = ['entity_type', 'code'];

                $table->id();

                if (Utils::isTenantEnabled()) {
                    $table->foreignId(config('custom-fields.column_names.tenant_foreign_key'))->nullable()->index();
                    $uniqueColumns[] = config('custom-fields.column_names.tenant_foreign_key');
                }

                $table->string('code');
                $table->string('name');
                $table->string('type');
                $table->string('entity_type');
                $table->unsignedBigInteger('sort_order')->nullable();

                $table->string('description')->nullable();

                $table->boolean('active')->default(true);
                $table->boolean('system_defined')->default(false);

                $table->unique($uniqueColumns);

                $table->softDeletes();
                $table->timestamps();
            });
        }

        // Add 'custom_field_section_id' and 'width' columns to 'custom_fields' table if they don't exist
        Schema::table('custom_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_fields', 'custom_field_section_id')) {
                $table->unsignedBigInteger('custom_field_section_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('custom_fields', 'width')) {
                $table->string('width')->nullable()->after('custom_field_section_id');
            }
        });


        $this->info('Database schema updated successfully.');
    }

    /**
     * Update existing data to match the new schema.
     */
    private function updateExistingData(): void
    {
        $this->info('Updating existing data...');

        // Use a transaction to ensure data integrity
        DB::transaction(function () {
            CustomField::query()
                ->whereNull('custom_field_section_id')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('entity_type')
                ->each(function ($customFields, $entityType) {
                    // Create a new section for each entity type
                    $section = CustomFieldSection::create([
                        'entity_type' => $entityType,
                        'name' => __('custom-fields::custom-fields.section.default.new_section'),
                        'code' => 'new_section',
                        'type' => CustomFieldSectionType::HEADLESS,
                    ]);

                    // Update each custom field to set the 'custom_field_section_id'
                    $customFields->each(fn($customField) => $customField->update([
                        'custom_field_section_id' => $section->id,
                        'width' => CustomFieldWidth::_100
                    ]));
                });
        });

        $this->info('Existing data updated successfully.');
    }
}
