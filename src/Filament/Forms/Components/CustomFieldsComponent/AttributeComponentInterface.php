<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Field;
use Relaticle\CustomFields\Models\CustomField;

interface AttributeComponentInterface
{
    public function make(CustomField $customField): Field;
}
