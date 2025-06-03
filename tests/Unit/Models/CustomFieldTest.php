<?php

declare(strict_types=1);

use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Enums\CustomFieldWidth;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Models\CustomFieldOption;
use Relaticle\CustomFields\Models\CustomFieldSection;
use Relaticle\CustomFields\Models\CustomFieldValue;

it('can create a custom field', function () {
    $customField = CustomField::create([
        'name' => 'Test Field',
        'code' => 'test_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
        'width' => CustomFieldWidth::_100,
    ]);

    expect($customField)->toBeInstanceOf(CustomField::class);
    expect($customField->name)->toBe('Test Field');
    expect($customField->code)->toBe('test_field');
    expect($customField->type)->toBe(CustomFieldType::TEXT);
    expect($customField->entity_type)->toBe('App\\Models\\User');
    expect($customField->width)->toBe(CustomFieldWidth::_100);
});

it('has correct default attributes', function () {
    $customField = new CustomField();
    
    expect($customField->width)->toBe(CustomFieldWidth::_100);
});

it('casts attributes correctly', function () {
    $customField = CustomField::create([
        'name' => 'Test Field',
        'code' => 'test_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
        'active' => true,
        'system_defined' => false,
    ]);

    expect($customField->type)->toBeInstanceOf(CustomFieldType::class);
    expect($customField->width)->toBeInstanceOf(CustomFieldWidth::class);
    expect($customField->active)->toBeBool();
    expect($customField->system_defined)->toBeBool();
    expect($customField->active)->toBeTrue();
    expect($customField->system_defined)->toBeFalse();
});

it('belongs to a section', function () {
    $section = CustomFieldSection::create([
        'name' => 'Test Section',
        'code' => 'test_section',
        'type' => 'section',
        'entity_type' => 'App\\Models\\User',
    ]);

    $customField = CustomField::create([
        'name' => 'Test Field',
        'code' => 'test_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
        'custom_field_section_id' => $section->id,
    ]);

    expect($customField->section)->toBeInstanceOf(CustomFieldSection::class);
    expect($customField->section->id)->toBe($section->id);
});

it('has many values', function () {
    $customField = CustomField::create([
        'name' => 'Test Field',
        'code' => 'test_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
    ]);

    $value1 = CustomFieldValue::create([
        'custom_field_id' => $customField->id,
        'entity_type' => 'App\\Models\\User',
        'entity_id' => 1,
        'text_value' => 'Test Value 1',
    ]);

    $value2 = CustomFieldValue::create([
        'custom_field_id' => $customField->id,
        'entity_type' => 'App\\Models\\User',
        'entity_id' => 2,
        'text_value' => 'Test Value 2',
    ]);

    expect($customField->values)->toHaveCount(2);
    expect($customField->values->first())->toBeInstanceOf(CustomFieldValue::class);
});

it('has many options', function () {
    $customField = CustomField::create([
        'name' => 'Test Field',
        'code' => 'test_field',
        'type' => CustomFieldType::SELECT,
        'entity_type' => 'App\\Models\\User',
    ]);

    $option1 = CustomFieldOption::create([
        'custom_field_id' => $customField->id,
        'name' => 'Option 1',
    ]);

    $option2 = CustomFieldOption::create([
        'custom_field_id' => $customField->id,
        'name' => 'Option 2',
    ]);

    expect($customField->options)->toHaveCount(2);
    expect($customField->options->first())->toBeInstanceOf(CustomFieldOption::class);
});

it('can determine if system defined', function () {
    $systemField = CustomField::create([
        'name' => 'System Field',
        'code' => 'system_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
        'system_defined' => true,
    ]);

    $userField = CustomField::create([
        'name' => 'User Field',
        'code' => 'user_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
        'system_defined' => false,
    ]);

    expect($systemField->isSystemDefined())->toBeTrue();
    expect($userField->isSystemDefined())->toBeFalse();
});

it('uses custom table name from config', function () {
    config(['custom-fields.table_names.custom_fields' => 'my_custom_fields']);
    
    $customField = new CustomField();
    
    expect($customField->getTable())->toBe('my_custom_fields');
});

it('applies global scopes', function () {
    // Create both active and inactive fields
    $activeField = CustomField::create([
        'name' => 'Active Field',
        'code' => 'active_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
        'active' => true,
    ]);

    $inactiveField = CustomField::create([
        'name' => 'Inactive Field',
        'code' => 'inactive_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
        'active' => false,
    ]);

    // Check that we can retrieve both fields without scope
    $allFieldsWithoutScope = CustomField::withoutGlobalScopes()->get();
    expect($allFieldsWithoutScope)->toHaveCount(2);

    // Check that active field exists
    expect($activeField->active)->toBeTrue();
    expect($inactiveField->active)->toBeFalse();
}); 