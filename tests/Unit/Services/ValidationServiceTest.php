<?php

declare(strict_types=1);

use Relaticle\CustomFields\Data\CustomFieldSettingsData;
use Relaticle\CustomFields\Data\ValidationRuleData;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Enums\CustomFieldValidationRule;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Services\ValidationService;
use Spatie\LaravelData\DataCollection;

beforeEach(function () {
    $this->validationService = app(ValidationService::class);
});

it('correctly identifies required fields', function () {
    $requiredField = new CustomField;
    $requiredField->validation_rules = new DataCollection(ValidationRuleData::class, [
        new ValidationRuleData(name: CustomFieldValidationRule::REQUIRED->value),
    ]);

    $optionalField = new CustomField;
    $optionalField->validation_rules = new DataCollection(ValidationRuleData::class, [
        new ValidationRuleData(name: CustomFieldValidationRule::STRING->value),
    ]);

    expect($this->validationService->isRequired($requiredField))->toBeTrue()
        ->and($this->validationService->isRequired($optionalField))->toBeFalse();
});

it('can handle empty validation rules', function () {
    $field = new CustomField;
    $field->validation_rules = new DataCollection(ValidationRuleData::class, []);

    expect($this->validationService->isRequired($field))->toBeFalse();
});

it('can identify fields with specific validation rules', function () {
    $field = new CustomField;
    $field->validation_rules = new DataCollection(ValidationRuleData::class, [
        new ValidationRuleData(name: CustomFieldValidationRule::MAX->value, parameters: ['100']),
        new ValidationRuleData(name: CustomFieldValidationRule::MIN->value, parameters: ['10']),
    ]);

    $hasMaxRule = $field->validation_rules->toCollection()
        ->contains('name', CustomFieldValidationRule::MAX->value);

    $hasMinRule = $field->validation_rules->toCollection()
        ->contains('name', CustomFieldValidationRule::MIN->value);

    expect($hasMaxRule)->toBeTrue();
    expect($hasMinRule)->toBeTrue();
});

it('can work with different field types', function () {
    $textField = new CustomField;
    $textField->type = CustomFieldType::TEXT;
    $textField->validation_rules = new DataCollection(ValidationRuleData::class, []);

    $numberField = new CustomField;
    $numberField->type = CustomFieldType::NUMBER;
    $numberField->validation_rules = new DataCollection(ValidationRuleData::class, []);

    expect($textField->type)->toBe(CustomFieldType::TEXT);
    expect($numberField->type)->toBe(CustomFieldType::NUMBER);
});

it('can handle settings data', function () {
    $field = new CustomField;
    $field->settings = createCustomFieldSettings(['encrypted' => true]);

    // The settings will be cast to CustomFieldSettingsData by the model
    expect($field->settings)->toBeArray();
    expect($field->settings['encrypted'])->toBeTrue();
});
