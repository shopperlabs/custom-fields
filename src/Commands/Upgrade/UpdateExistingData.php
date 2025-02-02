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
            $customFields = CustomField::query()
                ->whereNull('custom_field_section_id')
                ->select('entity_type', 'tenant_id');

            if ($customFields->doesntExist()) {
                $command->info('No custom fields found that require updating.');
                return;
            }

            foreach ($customFields->get() as $customField) {
                if ($isDryRun) {
                    $command->line("Custom field `{$customField['name']}` will be moved to a new section.");
                } else {
                    $sectionData = [
                        'entity_type' => $customField['entity_type'],
                        'name' => __('custom-fields::custom-fields.section.default.new_section'),
                        'code' => 'new_section',
                        'type' => CustomFieldSectionType::HEADLESS,
                        'tenant_id' => $customField['tenant_id'],
                    ];

                    $section = CustomFieldSection::create($sectionData);

                    $customField->update([
                        'custom_field_section_id' => $section->id,
                        'width' => CustomFieldWidth::_100,
                    ]);

                    $command->line("Custom field `{$customField['name']}` has been moved to a new section.");
                }
            }

            $command->newLine(2);
            $command->info('Existing data update step completed.');
        });

        return $next($command);
    }
}
