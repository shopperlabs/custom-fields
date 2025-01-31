<?php

namespace Relaticle\CustomFields\Commands\Upgrade;

use Closure;
use Illuminate\Support\Facades\DB;
use Relaticle\CustomFields\Commands\UpgradeCommand;
use Relaticle\CustomFields\Enums\CustomFieldSectionType;
use Relaticle\CustomFields\Enums\CustomFieldWidth;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Models\CustomFieldSection;

class UpdateExistingData
{
    public function handle(UpgradeCommand $command, Closure $next): mixed
    {
        $isDryRun = $command->isDryRun();

        $command->info('--- Updating existing data...');
        $command->newLine();

        DB::transaction(function () use ($command, $isDryRun): void {
            $entityTypes = CustomField::query()
                ->whereNull('custom_field_section_id')
                ->pluck('entity_type');

            if ($entityTypes->isEmpty()) {
                $command->info('No custom fields found that require updating.');
                return;
            }

            $command->info('Updating custom fields for the following entity types:');
            $command->line($entityTypes->implode(', '));
            $command->newLine();

            $progressBar = $command->output->createProgressBar($entityTypes->count());
            $progressBar->start();

            foreach ($entityTypes as $entityType) {
                if ($isDryRun) {
                    $command->line("A new section would be created for entity type `{$entityType}`.");
                } else {
                    $section = CustomFieldSection::create([
                        'entity_type' => $entityType,
                        'name' => __('custom-fields::custom-fields.section.default.new_section'),
                        'code' => 'new_section',
                        'type' => CustomFieldSectionType::HEADLESS,
                    ]);

                    CustomField::whereNull('custom_field_section_id')
                        ->where('entity_type', $entityType)
                        ->update([
                            'custom_field_section_id' => $section->id,
                            'width' => CustomFieldWidth::_100,
                        ]);

                    $command->line("Custom fields for entity type `{$entityType}` have been updated.");
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            $command->newLine(2);
            $command->info('Existing data update step completed.');
        });

        return $next($command);
    }
}
