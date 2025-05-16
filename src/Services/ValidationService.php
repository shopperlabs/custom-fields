<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Services;

use Illuminate\Support\Facades\Cache;
use Relaticle\CustomFields\Data\ValidationRuleData;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Enums\CustomFieldValidationRule;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Support\DatabaseFieldConstraints;
use Spatie\LaravelData\DataCollection;

/**
 * Service for handling field validation rules and constraints.
 */
final class ValidationService
{
    /**
     * Cache key prefix for storing validation rules.
     */
    private const CACHE_KEY_PREFIX = 'custom_fields_validation_rules_';
    
    /**
     * Cache TTL in seconds (1 hour).
     */
    private const CACHE_TTL = 3600;

    /**
     * Get all validation rules for a custom field, applying both:
     * - User-defined validation rules from the field configuration
     * - Database field constraints based on field type
     * - Special handling for numeric values to prevent database errors
     *
     * Returns a combined array of validation rules in Laravel validator format.
     *
     * @param CustomField $customField The custom field to get validation rules for
     * @return array<int, string> Combined array of validation rules
     */
    public function getValidationRules(CustomField $customField): array
    {
        // Try to get rules from cache first
        $cacheKey = $this->getCacheKey($customField);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($customField) {
            // Convert user rules to Laravel validator format
            $userRules = $this->convertUserRulesToValidatorFormat($customField->validation_rules);
            
            // Get database constraint rules
            $isEncrypted = $customField->settings?->encrypted ?? false;
            $databaseRules = $this->getDatabaseValidationRules($customField->type, $isEncrypted);
            
            // Determine which rules take precedence
            return $this->mergeValidationRules($userRules, $databaseRules, $customField->type);
        });
    }
    
    /**
     * Check if a field is required based on its validation rules.
     * 
     * @param CustomField $customField The custom field to check
     * @return bool True if the field is required
     */
    public function isRequired(CustomField $customField): bool
    {
        return $customField->validation_rules->toCollection()
            ->contains('name', CustomFieldValidationRule::REQUIRED->value);
    }

    /**
     * Convert user validation rules from DataCollection format to Laravel validator format.
     *
     * @param DataCollection<int, ValidationRuleData>|null $rules The validation rules to convert
     * @return array<int, string> The converted rules
     */
    private function convertUserRulesToValidatorFormat(?DataCollection $rules): array
    {
        if (!$rules instanceof DataCollection || $rules->toCollection()->isEmpty()) {
            return [];
        }
        
        return $rules->toCollection()
            ->map(function (ValidationRuleData $ruleData): string {
                if (empty($ruleData->parameters)) {
                    return $ruleData->name;
                }
                
                return $ruleData->name . ':' . implode(',', $ruleData->parameters);
            })
            ->toArray();
    }
    
    /**
     * Get all database validation rules for a specific field type.
     * 
     * @param CustomFieldType $fieldType The field type
     * @param bool $isEncrypted Whether the field is encrypted
     * @return array<int, string> Array of validation rules
     */
    private function getDatabaseValidationRules(CustomFieldType $fieldType, bool $isEncrypted = false): array
    {
        // Get base database rules for this field type
        $dbRules = DatabaseFieldConstraints::getValidationRulesForFieldType($fieldType, $isEncrypted);
        
        // For JSON fields, add array validation rules
        if ($fieldType->hasMultipleValues()) {
            $jsonRules = DatabaseFieldConstraints::getJsonValidationRules($fieldType, $isEncrypted);
            return array_merge($dbRules, $jsonRules);
        }
        
        return $dbRules;
    }
    
    /**
     * Merge user-defined rules with database constraint rules, applying appropriate precedence logic.
     * Ensures that user-defined rules that are stricter than database constraints are preserved.
     * 
     * @param array<int, string> $userRules User-defined validation rules
     * @param array<int, string> $databaseRules Database constraint validation rules
     * @param CustomFieldType $fieldType The field type
     * @return array<int, string> Merged validation rules
     */
    private function mergeValidationRules(array $userRules, array $databaseRules, CustomFieldType $fieldType): array
    {
        // Get constraints for this field type
        $dbConstraints = DatabaseFieldConstraints::getConstraintsForFieldType($fieldType);
        
        // If we have constraints, use the constraint-aware merge function
        if (!empty($dbConstraints)) {
            // Important: we pass userRules first to ensure they take precedence
            // when they're stricter than system constraints
            return DatabaseFieldConstraints::mergeConstraintsWithRules($dbConstraints, $userRules);
        }
        
        // Otherwise, simply combine the rules, with user rules taking precedence
        return $this->combineRules($userRules, $databaseRules);
    }
    
    /**
     * Combine two sets of rules, removing duplicates but preserving rule precedence.
     * 
     * @param array<int, string> $primaryRules Rules that take precedence
     * @param array<int, string> $secondaryRules Rules that are overridden by primary rules
     * @return array<int, string> Combined rules
     */
    private function combineRules(array $primaryRules, array $secondaryRules): array
    {
        // Extract rule names (without parameters) from primary rules
        $primaryRuleNames = array_map(function (string $rule) {
            return explode(':', $rule, 2)[0];
        }, $primaryRules);
        
        // Filter secondary rules to only include those that don't conflict with primary rules
        $filteredSecondaryRules = array_filter($secondaryRules, function (string $rule) use ($primaryRuleNames) {
            $ruleName = explode(':', $rule, 2)[0];
            return !in_array($ruleName, $primaryRuleNames);
        });
        
        // Combine the rules, with primary rules first
        return array_merge($primaryRules, $filteredSecondaryRules);
    }
    
    /**
     * Generate a cache key for a custom field's validation rules.
     * 
     * @param CustomField $customField The custom field
     * @return string The cache key
     */
    private function getCacheKey(CustomField $customField): string
    {
        $isEncrypted = $customField->settings?->encrypted ?? false;
        return self::CACHE_KEY_PREFIX . $customField->id . '_' . $customField->type->value . '_' . ($isEncrypted ? '1' : '0');
    }
    
    /**
     * Clear the validation rules cache for a specific custom field.
     * 
     * @param CustomField $customField The custom field
     * @return void
     */
    public function clearCache(CustomField $customField): void
    {
        $cacheKey = $this->getCacheKey($customField);
        Cache::forget($cacheKey);
    }
    
    /**
     * Clear all validation rule caches.
     * 
     * @return void
     */
    public function clearAllCaches(): void
    {
        // Pattern match to clear all validation rule caches
        // This is a simplified approach - in production you might want a more targeted cache clear
        Cache::flush(); 
    }
}
