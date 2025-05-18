<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Tests\Unit\Enums;

use Carbon\Carbon;
use Relaticle\CustomFields\Enums\CustomFieldValidationRule;
use Relaticle\CustomFields\Tests\TestCase;

class CustomFieldValidationRuleTest extends TestCase
{
    /** @test */
    public function it_returns_validation_rules_for_numeric_parameters()
    {
        $rules = CustomFieldValidationRule::SIZE->getParameterValidationRule();
        $this->assertContains('numeric', $rules);
        $this->assertContains('min:0', $rules);
        
        $rules = CustomFieldValidationRule::MIN->getParameterValidationRule();
        $this->assertContains('numeric', $rules);
        $this->assertContains('min:0', $rules);
        
        $rules = CustomFieldValidationRule::MULTIPLE_OF->getParameterValidationRule();
        $this->assertContains('numeric', $rules);
        $this->assertContains('gt:0', $rules);
    }
    
    /** @test */
    public function it_returns_validation_rules_for_between_parameters()
    {
        $rules = CustomFieldValidationRule::BETWEEN->getParameterValidationRule();
        $this->assertContains('numeric', $rules);
        
        // Second parameter (max)
        $rules = CustomFieldValidationRule::BETWEEN->getParameterValidationRule(1);
        $this->assertContains('numeric', $rules);
        
        // Check for a validation callback since we're not using 'gte:parameters.0.value' directly
        $hasValidationCallback = false;
        foreach ($rules as $rule) {
            if (is_callable($rule)) {
                $hasValidationCallback = true;
                break;
            }
        }
        $this->assertTrue($hasValidationCallback, 'Second parameter should have validation callback');
    }
    
    /** @test */
    public function it_returns_validation_rules_for_digits_between_parameters()
    {
        $rules = CustomFieldValidationRule::DIGITS_BETWEEN->getParameterValidationRule();
        $this->assertContains('integer', $rules);
        $this->assertContains('min:1', $rules);
        
        // Second parameter (max)
        $rules = CustomFieldValidationRule::DIGITS_BETWEEN->getParameterValidationRule(1);
        $this->assertContains('integer', $rules);
        
        // Check for a validation callback since we're not using 'gte:parameters.0.value' directly
        $hasValidationCallback = false;
        foreach ($rules as $rule) {
            if (is_callable($rule)) {
                $hasValidationCallback = true;
                break;
            }
        }
        $this->assertTrue($hasValidationCallback, 'Second parameter should have validation callback');
    }
    
    /** @test */
    public function it_returns_validation_rules_for_date_parameters()
    {
        $rules = CustomFieldValidationRule::DATE_FORMAT->getParameterValidationRule();
        $this->assertContains('string', $rules);
        
        $rules = CustomFieldValidationRule::AFTER->getParameterValidationRule();
        $this->assertContains('required', $rules);
        $this->assertTrue(is_callable($rules[1]));
    }
    
    /** @test */
    public function it_returns_validation_rules_for_regex_parameters()
    {
        $rules = CustomFieldValidationRule::REGEX->getParameterValidationRule();
        $this->assertContains('string', $rules);
        $this->assertTrue(is_callable($rules[2]));
    }
    
    /** @test */
    public function it_returns_suggestions_for_date_format()
    {
        $suggestions = CustomFieldValidationRule::DATE_FORMAT->getParameterSuggestions();
        $this->assertArrayHasKey('Y-m-d', $suggestions);
        $this->assertArrayHasKey('Y/m/d', $suggestions);
        $this->assertArrayHasKey('d-m-Y', $suggestions);
    }
    
    /** @test */
    public function it_returns_suggestions_for_date_parameters()
    {
        $suggestions = CustomFieldValidationRule::AFTER->getParameterSuggestions();
        $this->assertArrayHasKey('today', $suggestions);
        $this->assertArrayHasKey('tomorrow', $suggestions);
    }
    
    /** @test */
    public function it_returns_suggestions_for_mime_types()
    {
        $suggestions = CustomFieldValidationRule::MIMES->getParameterSuggestions();
        $this->assertArrayHasKey('jpg,jpeg,png,gif', $suggestions);
        $this->assertArrayHasKey('pdf,doc,docx', $suggestions);
    }
    
    /** @test */
    public function it_returns_help_text_for_parameters()
    {
        $this->assertEquals(
            __('custom-fields::custom-fields.validation.parameter_help.size'),
            CustomFieldValidationRule::SIZE->getParameterHelpText()
        );
        
        $this->assertEquals(
            __('custom-fields::custom-fields.validation.parameter_help.min'),
            CustomFieldValidationRule::MIN->getParameterHelpText()
        );
        
        $this->assertEquals(
            __('custom-fields::custom-fields.validation.parameter_help.regex'),
            CustomFieldValidationRule::REGEX->getParameterHelpText()
        );
    }
    
    /** @test */
    public function it_returns_different_help_text_for_each_parameter_of_multi_parameter_rules()
    {
        $minHelpText = CustomFieldValidationRule::BETWEEN->getParameterHelpText(0);
        $maxHelpText = CustomFieldValidationRule::BETWEEN->getParameterHelpText(1);
        
        $this->assertNotEquals($minHelpText, $maxHelpText);
        $this->assertEquals(
            __('custom-fields::custom-fields.validation.parameter_help.between.min'),
            $minHelpText
        );
        $this->assertEquals(
            __('custom-fields::custom-fields.validation.parameter_help.between.max'),
            $maxHelpText
        );
    }
    
    /** @test */
    public function it_can_get_parameter_validation_rules_with_static_method()
    {
        $rules = CustomFieldValidationRule::getParameterValidationRuleFor('min');
        $this->assertContains('numeric', $rules);
        
        $rules = CustomFieldValidationRule::getParameterValidationRuleFor('between', 1);
        $this->assertContains('numeric', $rules);
        
        // Check for a validation callback
        $hasValidationCallback = false;
        foreach ($rules as $rule) {
            if (is_callable($rule)) {
                $hasValidationCallback = true;
                break;
            }
        }
        $this->assertTrue($hasValidationCallback, 'Second parameter should have validation callback');
        
        $rules = CustomFieldValidationRule::getParameterValidationRuleFor(null);
        $this->assertContains('string', $rules);
    }
    
    /** @test */
    public function it_can_get_parameter_suggestions_with_static_method()
    {
        $suggestions = CustomFieldValidationRule::getParameterSuggestionsFor('date_format');
        $this->assertArrayHasKey('Y-m-d', $suggestions);
        
        $suggestions = CustomFieldValidationRule::getParameterSuggestionsFor(null);
        $this->assertEmpty($suggestions);
    }
    
    /** @test */
    public function it_can_get_parameter_help_text_with_static_method()
    {
        $helpText = CustomFieldValidationRule::getParameterHelpTextFor('min');
        $this->assertEquals(
            __('custom-fields::custom-fields.validation.parameter_help.min'),
            $helpText
        );
        
        $helpText = CustomFieldValidationRule::getParameterHelpTextFor('between', 1);
        $this->assertEquals(
            __('custom-fields::custom-fields.validation.parameter_help.between.max'),
            $helpText
        );
        
        $helpText = CustomFieldValidationRule::getParameterHelpTextFor(null);
        $this->assertEquals(
            __('custom-fields::custom-fields.validation.parameter_help.default'),
            $helpText
        );
    }
    
    /** @test */
    public function it_can_normalize_numeric_parameter_values()
    {
        $this->assertEquals('10', CustomFieldValidationRule::normalizeParameterValue('min', '10'));
        $this->assertEquals('10.5', CustomFieldValidationRule::normalizeParameterValue('min', '10.5'));
        $this->assertEquals('10', CustomFieldValidationRule::normalizeParameterValue('min', '10.0'));
    }
    
    /** @test */
    public function it_can_normalize_date_parameter_values()
    {
        $todayString = Carbon::now()->format('Y-m-d');
        
        $this->assertEquals('today', CustomFieldValidationRule::normalizeParameterValue('after', 'today'));
        $this->assertEquals($todayString, CustomFieldValidationRule::normalizeParameterValue('after', $todayString));
        $this->assertEquals('invalid-date', CustomFieldValidationRule::normalizeParameterValue('after', 'invalid-date'));
    }
    
    /** @test */
    public function it_can_normalize_list_parameter_values()
    {
        $this->assertEquals('foo,bar,baz', CustomFieldValidationRule::normalizeParameterValue('in', ' foo,bar,baz '));
    }
    
    /** @test */
    public function it_enforces_exactly_two_parameters_for_between_rule()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        // This should throw an exception because between requires exactly 2 parameters
        CustomFieldValidationRule::BETWEEN->getParameterHelpText(2);
    }
    
    /** @test */
    public function it_validates_between_rule_parameters_correctly()
    {
        // First parameter (min value)
        $rules = CustomFieldValidationRule::BETWEEN->getParameterValidationRule(0);
        $this->assertContains('required', $rules);
        $this->assertContains('numeric', $rules);
        
        // Second parameter (max value)
        $rules = CustomFieldValidationRule::BETWEEN->getParameterValidationRule(1);
        $this->assertContains('required', $rules);
        $this->assertContains('numeric', $rules);
        
        // Test validation function exists
        $hasValidation = false;
        foreach ($rules as $rule) {
            if (is_callable($rule)) {
                $hasValidation = true;
                break;
            }
        }
        $this->assertTrue($hasValidation, 'Between rule should have a validation callback');
    }
    
    /** @test */
    public function it_validates_digits_between_rule_parameters_correctly()
    {
        // First parameter (min digits)
        $rules = CustomFieldValidationRule::DIGITS_BETWEEN->getParameterValidationRule(0);
        $this->assertContains('required', $rules);
        $this->assertContains('integer', $rules);
        $this->assertContains('min:1', $rules);
        
        // Second parameter (max digits)
        $rules = CustomFieldValidationRule::DIGITS_BETWEEN->getParameterValidationRule(1);
        $this->assertContains('required', $rules);
        $this->assertContains('integer', $rules);
    }
    
    /** @test */
    public function it_validates_decimal_rule_parameters_correctly()
    {
        // First parameter (min decimal places)
        $rules = CustomFieldValidationRule::DECIMAL->getParameterValidationRule(0);
        $this->assertContains('required', $rules);
        $this->assertContains('integer', $rules);
        $this->assertContains('min:0', $rules);
        
        // Second parameter (max decimal places)
        $rules = CustomFieldValidationRule::DECIMAL->getParameterValidationRule(1);
        $this->assertContains('required', $rules);
        $this->assertContains('integer', $rules);
    }
}