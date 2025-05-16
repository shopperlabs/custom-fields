<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Services\ValidationRules;

use Relaticle\CustomFields\Enums\CustomFieldType;

/**
 * Field type-specific validation rules generator.
 * Provides appropriate validation rules based on field type characteristics.
 */
final class FieldTypeValidator
{
    /**
     * Get validation rules specific to a field type.
     *
     * @param CustomFieldType $fieldType The field type
     * @return array<int, string> Type-specific validation rules
     */
    public static function getTypeSpecificRules(CustomFieldType $fieldType): array
    {
        return match ($fieldType) {
            CustomFieldType::TEXT, 
            CustomFieldType::TEXTAREA, 
            CustomFieldType::RICH_EDITOR, 
            CustomFieldType::MARKDOWN_EDITOR,
            CustomFieldType::COLOR_PICKER,
            CustomFieldType::LINK => ['string'],
            
            CustomFieldType::NUMBER => ['numeric', 'integer'],
            
            CustomFieldType::CURRENCY => ['numeric', 'decimal:0,2'],
            
            CustomFieldType::DATE => ['date'],
            
            CustomFieldType::DATE_TIME => ['datetime'],
            
            CustomFieldType::CHECKBOX,
            CustomFieldType::TOGGLE => ['boolean'],
            
            CustomFieldType::CHECKBOX_LIST,
            CustomFieldType::TOGGLE_BUTTONS,
            CustomFieldType::MULTI_SELECT,
            CustomFieldType::TAGS_INPUT => ['array'],
            
            CustomFieldType::SELECT,
            CustomFieldType::RADIO => ['string'], // Select and radio fields store single values
        };
    }
    
    /**
     * Check if a field type requires array validation.
     *
     * @param CustomFieldType $fieldType The field type
     * @return bool True if field type stores multiple values
     */
    public static function requiresArrayValidation(CustomFieldType $fieldType): bool
    {
        return $fieldType->hasMultipleValues();
    }
    
    /**
     * Get maximum allowed array items for field types that store multiple values.
     *
     * @param CustomFieldType $fieldType The field type
     * @param bool $isEncrypted Whether the field is encrypted
     * @return int|null Maximum number of items or null if not applicable
     */
    public static function getMaxArrayItems(CustomFieldType $fieldType, bool $isEncrypted = false): ?int
    {
        if (!self::requiresArrayValidation($fieldType)) {
            return null;
        }
        
        // Base maximum items
        $maxItems = match ($fieldType) {
            CustomFieldType::CHECKBOX_LIST => 50,
            CustomFieldType::TOGGLE_BUTTONS => 20,
            CustomFieldType::MULTI_SELECT => 100,
            CustomFieldType::TAGS_INPUT => 200,
            default => null,
        };
        
        // Reduce for encrypted fields
        if ($isEncrypted && $maxItems !== null) {
            return (int) ($maxItems * 0.75); // 25% reduction for encrypted fields
        }
        
        return $maxItems;
    }
    
    /**
     * Check if a field type can be encrypted.
     *
     * @param CustomFieldType $fieldType The field type
     * @return bool True if field can be encrypted
     */
    public static function canBeEncrypted(CustomFieldType $fieldType): bool
    {
        return in_array($fieldType, [
            CustomFieldType::TEXT,
            CustomFieldType::TEXTAREA,
            CustomFieldType::RICH_EDITOR,
            CustomFieldType::MARKDOWN_EDITOR,
            CustomFieldType::LINK,
        ]);
    }
}
