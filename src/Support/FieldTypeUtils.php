<?php

namespace Relaticle\CustomFields\Support;

class FieldTypeUtils
{
    public static function isDatePickerNative(): bool
    {
        return config('custom-fields.field_types_configuration.date_picker.native', true);
    }

    public static function getDateFormat(): string
    {
        return config('custom-fields.field_types_configuration.date_picker.format', 'Y-m-d');
    }

    public static function getDateDisplayFormat(): string
    {
        return config('custom-fields.field_types_configuration.date_picker.display_format', 'M j, Y');
    }
}
