<?php

namespace Relaticle\CustomFields\Support;

class FieldTypeUtils
{
    public static function isDatePickerNative(): bool
    {
        return config('custom-fields.field_types_configuration.date.native', true);
    }

    public static function getDateFormat(): string
    {
        return config('custom-fields.field_types_configuration.date.format', 'Y-m-d');
    }

    public static function getDateDisplayFormat(): ?string
    {
        return config('custom-fields.field_types_configuration.date.display_format', 'M j, Y');
    }

    public static function isDateTimePickerNative(): bool
    {
        return config('custom-fields.field_types_configuration.date.native', true);
    }

    public static function getDateTimeFormat(): string
    {
        return config('custom-fields.field_types_configuration.date_time.format', 'Y-m-d');
    }

    public static function getDateTimeDisplayFormat(): ?string
    {
        return config('custom-fields.field_types_configuration.date_time.display_format', 'M j, Y');
    }
}
