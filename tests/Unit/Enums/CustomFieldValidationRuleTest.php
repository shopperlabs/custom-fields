<?php

declare(strict_types=1);

use Relaticle\CustomFields\Enums\CustomFieldValidationRule;

it('has basic validation rule cases', function () {
    $cases = CustomFieldValidationRule::cases();
    expect($cases)->not->toBeEmpty();
    expect($cases[0])->toBeInstanceOf(CustomFieldValidationRule::class);
});

it('can get labels for validation rules', function () {
    $label = CustomFieldValidationRule::REQUIRED->getLabel();
    expect($label)->toBeString();
    expect($label)->not->toBeEmpty();
});

it('can get descriptions for validation rules', function () {
    $description = CustomFieldValidationRule::REQUIRED->getDescription();
    expect($description)->toBeString();
});

it('can check if rule has parameters', function () {
    expect(CustomFieldValidationRule::REQUIRED->hasParameter())->toBeFalse();
    expect(CustomFieldValidationRule::MIN->hasParameter())->toBeTrue();
    expect(CustomFieldValidationRule::BETWEEN->hasParameter())->toBeTrue();
});

it('can get allowed parameter count', function () {
    expect(CustomFieldValidationRule::REQUIRED->allowedParameterCount())->toBe(0);
    expect(CustomFieldValidationRule::MIN->allowedParameterCount())->toBe(1);
    expect(CustomFieldValidationRule::BETWEEN->allowedParameterCount())->toBe(2);
});

it('can get help text for parameters', function () {
    $helpText = CustomFieldValidationRule::REGEX->getParameterHelpText();
    expect($helpText)->toBeString();
});

it('can get help text with static method', function () {
    $helpText = CustomFieldValidationRule::getParameterHelpTextFor('regex');
    expect($helpText)->toBeString();
    
    $helpText = CustomFieldValidationRule::getParameterHelpTextFor(null);
    expect($helpText)->toBeString();
});

it('can normalize parameter values', function () {
    // Test string normalization
    $normalized = CustomFieldValidationRule::normalizeParameterValue('regex', '/test/');
    expect($normalized)->toBe('/test/');
    
    // Test that it returns the input for unknown rules
    $normalized = CustomFieldValidationRule::normalizeParameterValue('unknown', 'value');
    expect($normalized)->toBe('value');
});

it('can get validation rules for string parameters', function () {
    $rules = CustomFieldValidationRule::REGEX->getParameterValidationRule(0);
    expect($rules)->toContain('required');
    expect($rules)->toContain('string');
});

it('can get validation rules with static method for string rules', function () {
    $rules = CustomFieldValidationRule::getParameterValidationRuleFor('regex');
    expect($rules)->toContain('required');
    expect($rules)->toContain('string');
    
    $rules = CustomFieldValidationRule::getParameterValidationRuleFor(null);
    expect($rules)->toContain('string');
}); 