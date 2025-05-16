# Large Number Handling in Custom Fields

## Problem Description

When dealing with very large integers (close to the MySQL BIGINT limits), the system was encountering "numeric value out of range" errors like this:

```
SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column 'integer_value' at row 1 
(Connection: mysql, SQL: update `custom_field_values` set `integer_value` = -9.2233720368548E+18 where `id` = 100001)
```

Additionally, there was a type error where scientific notation was causing `float` values to be returned instead of the required `int` type:

```
Relaticle\CustomFields\Support\SafeValueConverter::toSafeInteger(): Return value must be of type ?int, float returned
```

This happens because scientific notation values like `-9.2233720368548E+18` may slightly exceed the MySQL BIGINT range:
- Min value: -9,223,372,036,854,775,808
- Max value: 9,223,372,036,854,775,807

## Solution

We've implemented several improvements to handle large numbers properly:

### 1. SafeValueConverter

The `SafeValueConverter` class provides a safe way to convert values to database-compatible formats:

- It safely converts string numbers including scientific notation
- It automatically clamps values that exceed database limits 
- It strictly enforces integer return types for integer fields
- It provides type-specific conversions for different field types

```php
// Example usage
$safeIntegerValue = SafeValueConverter::toDbSafe($largeNumber, CustomFieldType::NUMBER);
```

### 2. CustomFieldValue Enhancements

The `setValue` method in `CustomFieldValue` now uses the `SafeValueConverter` to ensure all values are database-safe before saving.

```php
public function setValue(mixed $value): void
{
    $column = $this->getValueColumn($this->customField->type);
    
    // Convert the value to a database-safe format based on the field type
    $safeValue = SafeValueConverter::toDbSafe(
        $value, 
        $this->customField->type
    );
    
    $this->$column = $safeValue;
}
```

### 3. Improved Validation Rules

The validation rules now properly handle numeric values in all formats:

- Added proper handling for scientific notation
- Added numeric and integer validation for number fields
- Used string representations for min/max values to avoid floating point issues

## Testing

The solution has been verified with comprehensive tests in `SafeValueConverterTest`, ensuring:
- Correct handling of normal integers
- Proper parsing of scientific notation
- Clamping of values that exceed BIGINT bounds
- Appropriate handling of invalid values
- Correct conversion based on field type

## Future Improvements

For future releases, consider:
1. Adding a warning when a value is clamped to database limits
2. Supporting custom behavior for handling out-of-range values
3. Adding more specialized validation for currency and decimal fields
