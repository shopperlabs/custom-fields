<?php

namespace Relaticle\CustomFields\Observers;

use Relaticle\CustomFields\Models\CustomField;

class CustomFieldObserver
{
    /**
     * @param CustomField $customField
     * @return void
     */
    public function deleted(CustomField $customField): void
    {
        // Delete the custom field options
        $customField->options()->delete();

        // Delete the custom field values
        $customField->values()->delete();
    }
}
