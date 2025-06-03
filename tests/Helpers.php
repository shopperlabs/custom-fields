<?php

declare(strict_types=1);

use Relaticle\CustomFields\Data\CustomFieldSettingsData;

if (!function_exists('createCustomFieldSettings')) {
    /**
     * Create CustomField settings data properly formatted for the model.
     * 
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    function createCustomFieldSettings(array $overrides = []): array
    {
        return [
            'visible_in_list' => $overrides['visible_in_list'] ?? true,
            'list_toggleable_hidden' => $overrides['list_toggleable_hidden'] ?? null,
            'visible_in_view' => $overrides['visible_in_view'] ?? true,
            'searchable' => $overrides['searchable'] ?? false,
            'encrypted' => $overrides['encrypted'] ?? false,
        ];
    }
} 