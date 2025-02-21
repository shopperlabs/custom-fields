<?php

namespace Relaticle\CustomFields\Observers;

use Relaticle\CustomFields\Models\CustomFieldSection;

class CustomFieldSectionObserver
{
    /**
     * @param CustomFieldSection $customFieldSection
     * @return void
     */
    public function deleted(CustomFieldSection $customFieldSection): void
    {
        $customFieldSection->fields()->delete();
    }
}
