<?php

namespace Relaticle\CustomFields\Tests\Unit\Services;

use Relaticle\CustomFields\Data\ValidationRuleData;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Enums\CustomFieldValidationRule;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Services\ValidationService;
use Spatie\LaravelData\DataCollection;
use Relaticle\CustomFields\Tests\TestCase;

class ValidationServiceTest extends TestCase
{
    protected ValidationService $validationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validationService = new ValidationService();
    }

    /** @test */
    public function it_correctly_identifies_required_fields()
    {
        $requiredField = new CustomField([
            'validation_rules' => new DataCollection(ValidationRuleData::class, [
                new ValidationRuleData(name: CustomFieldValidationRule::REQUIRED->value),
            ]),
        ]);

        $optionalField = new CustomField([
            'validation_rules' => new DataCollection(ValidationRuleData::class, [
                new ValidationRuleData(name: CustomFieldValidationRule::STRING->value),
            ]),
        ]);

        $this->assertTrue($this->validationService->isRequired($requiredField));
        $this->assertFalse($this->validationService->isRequired($optionalField));
    }

    /** @test */
    public function it_correctly_merges_user_rules_with_database_constraints()
    {
        $textField = CustomField::factory()->create([
            'type' => CustomFieldType::TEXT,
            'validation_rules' => new DataCollection(ValidationRuleData::class, [
                new ValidationRuleData(name: CustomFieldValidationRule::MAX->value, parameters: ['100']),
            ]),
        ]);

        $rules = $this->validationService->getValidationRules($textField);
        
        // The database constraint for TEXT is usually higher than 100, 
        // so user's rule should take precedence
        $this->assertContains('max:100', $rules);
        $this->assertContains('string', $rules); // Added by database constraints
    }

    /** @test */
    public function it_applies_stricter_rules_when_merging_constraints()
    {
        $textField = CustomField::factory()->create([
            'type' => CustomFieldType::TEXT,
            'validation_rules' => new DataCollection(ValidationRuleData::class, [
                new ValidationRuleData(name: CustomFieldValidationRule::MAX->value, parameters: ['100000']),
            ]),
        ]);

        $rules = $this->validationService->getValidationRules($textField);
        
        // The database constraint for text should be applied because it's stricter
        // than the user's rule of 100000
        $maxRule = collect($rules)->first(function ($rule) {
            return strpos($rule, 'max:') === 0;
        });
        
        $this->assertNotNull($maxRule);
        $maxValue = (int) substr($maxRule, 4);
        $this->assertLessThan(100000, $maxValue);
    }

    /** @test */
    public function it_adds_array_validation_for_multi_value_fields()
    {
        $multiSelectField = CustomField::factory()->create([
            'type' => CustomFieldType::MULTI_SELECT,
            'validation_rules' => new DataCollection(ValidationRuleData::class, []),
        ]);

        $rules = $this->validationService->getValidationRules($multiSelectField);
        
        $this->assertContains('array', $rules);
        
        // Should contain max items limit for arrays
        $hasMaxRule = collect($rules)->contains(function ($rule) {
            return strpos($rule, 'max:') === 0;
        });
        
        $this->assertTrue($hasMaxRule);
    }

    /** @test */
    public function it_adjusts_limits_for_encrypted_fields()
    {
        $encryptedTextField = CustomField::factory()->create([
            'type' => CustomFieldType::TEXT,
            'settings' => ['encrypted' => true],
            'validation_rules' => new DataCollection(ValidationRuleData::class, []),
        ]);

        $nonEncryptedTextField = CustomField::factory()->create([
            'type' => CustomFieldType::TEXT,
            'settings' => ['encrypted' => false],
            'validation_rules' => new DataCollection(ValidationRuleData::class, []),
        ]);

        $encryptedRules = $this->validationService->getValidationRules($encryptedTextField);
        $nonEncryptedRules = $this->validationService->getValidationRules($nonEncryptedTextField);
        
        $getMaxValue = function ($rules) {
            $maxRule = collect($rules)->first(function ($rule) {
                return strpos($rule, 'max:') === 0;
            });
            
            return $maxRule ? (int) substr($maxRule, 4) : null;
        };
        
        $encryptedMax = $getMaxValue($encryptedRules);
        $nonEncryptedMax = $getMaxValue($nonEncryptedRules);
        
        $this->assertLessThan($nonEncryptedMax, $encryptedMax);
    }

    /** @test */
    public function it_respects_user_defined_values_smaller_than_system_limits()
    {
        // Create a NUMBER field with a small max value (system limit is much higher)
        $numberField = CustomField::factory()->create([
            'type' => CustomFieldType::NUMBER,
            'validation_rules' => new DataCollection(ValidationRuleData::class, [
                new ValidationRuleData(name: CustomFieldValidationRule::MAX->value, parameters: ['100']),
                new ValidationRuleData(name: CustomFieldValidationRule::MIN->value, parameters: ['10']),
            ]),
        ]);

        $rules = $this->validationService->getValidationRules($numberField);
        
        // Check that the user's max:100 is preserved (not replaced with system's BIGINT max)
        $this->assertContains('max:100', $rules);
        
        // Check that the user's min:10 is preserved (not replaced with system's BIGINT min)
        $this->assertContains('min:10', $rules);
        
        // Create a TEXT field with a small max value
        $textField = CustomField::factory()->create([
            'type' => CustomFieldType::TEXT,
            'validation_rules' => new DataCollection(ValidationRuleData::class, [
                new ValidationRuleData(name: CustomFieldValidationRule::MAX->value, parameters: ['50']),
            ]),
        ]);

        $textRules = $this->validationService->getValidationRules($textField);
        
        // Check that the user's max:50 is preserved (database TEXT limit is much higher)
        $this->assertContains('max:50', $textRules);
    }

    /** @test */
    public function it_applies_system_limits_when_user_values_exceed_them()
    {
        // Create a NUMBER field with a max value exceeding system limits
        $numberField = CustomField::factory()->create([
            'type' => CustomFieldType::NUMBER,
            'validation_rules' => new DataCollection(ValidationRuleData::class, [
                // PHP_INT_MAX + 1 as a string to avoid integer overflow
                new ValidationRuleData(name: CustomFieldValidationRule::MAX->value, parameters: ['9223372036854775808']),
            ]),
        ]);

        $rules = $this->validationService->getValidationRules($numberField);
        
        // The rule should be constrained to the system's max value
        $hasSystemMax = collect($rules)->contains(function ($rule) {
            return strpos($rule, 'max:') === 0 && 
                   $rule !== 'max:9223372036854775808';
        });
        
        $this->assertTrue($hasSystemMax, 'System should enforce maximum database limits');
    }
}
