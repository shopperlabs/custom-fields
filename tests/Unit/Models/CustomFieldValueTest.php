<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Models\CustomFieldValue;

it('can create a custom field value', function () {
    $customField = CustomField::create([
        'name' => 'Test Field',
        'code' => 'test_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
        'active' => true,
    ]);

    $value = CustomFieldValue::create([
        'custom_field_id' => $customField->id,
        'entity_type' => 'App\\Models\\User',
        'entity_id' => 1,
        'text_value' => 'Test Value',
    ]);

    expect($value)->toBeInstanceOf(CustomFieldValue::class);
    expect($value->custom_field_id)->toBe($customField->id);
    expect($value->entity_type)->toBe('App\\Models\\User');
    expect($value->entity_id)->toBe(1);
    expect($value->text_value)->toBe('Test Value');
});

it('returns correct value column for different types', function () {
    expect(CustomFieldValue::getValueColumn(CustomFieldType::TEXT))->toBe('text_value');
    expect(CustomFieldValue::getValueColumn(CustomFieldType::TEXTAREA))->toBe('text_value');
    expect(CustomFieldValue::getValueColumn(CustomFieldType::RICH_EDITOR))->toBe('text_value');
    expect(CustomFieldValue::getValueColumn(CustomFieldType::MARKDOWN_EDITOR))->toBe('text_value');
    
    expect(CustomFieldValue::getValueColumn(CustomFieldType::LINK))->toBe('string_value');
    expect(CustomFieldValue::getValueColumn(CustomFieldType::COLOR_PICKER))->toBe('string_value');
    
    expect(CustomFieldValue::getValueColumn(CustomFieldType::NUMBER))->toBe('integer_value');
    expect(CustomFieldValue::getValueColumn(CustomFieldType::RADIO))->toBe('integer_value');
    expect(CustomFieldValue::getValueColumn(CustomFieldType::SELECT))->toBe('integer_value');
    
    expect(CustomFieldValue::getValueColumn(CustomFieldType::CHECKBOX))->toBe('boolean_value');
    expect(CustomFieldValue::getValueColumn(CustomFieldType::TOGGLE))->toBe('boolean_value');
    
    expect(CustomFieldValue::getValueColumn(CustomFieldType::CHECKBOX_LIST))->toBe('json_value');
    expect(CustomFieldValue::getValueColumn(CustomFieldType::TOGGLE_BUTTONS))->toBe('json_value');
    expect(CustomFieldValue::getValueColumn(CustomFieldType::TAGS_INPUT))->toBe('json_value');
    expect(CustomFieldValue::getValueColumn(CustomFieldType::MULTI_SELECT))->toBe('json_value');
    
    expect(CustomFieldValue::getValueColumn(CustomFieldType::CURRENCY))->toBe('float_value');
    
    expect(CustomFieldValue::getValueColumn(CustomFieldType::DATE))->toBe('date_value');
    expect(CustomFieldValue::getValueColumn(CustomFieldType::DATE_TIME))->toBe('datetime_value');
});

it('can store different value types', function () {
    $customField = CustomField::create([
        'name' => 'Test Field',
        'code' => 'test_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
        'active' => true,
    ]);

    // Test text value
    $textValue = CustomFieldValue::create([
        'custom_field_id' => $customField->id,
        'entity_type' => 'App\\Models\\User',
        'entity_id' => 1,
        'text_value' => 'Hello World',
    ]);
    expect($textValue->text_value)->toBe('Hello World');

    // Test boolean value
    $boolValue = CustomFieldValue::create([
        'custom_field_id' => $customField->id,
        'entity_type' => 'App\\Models\\User',
        'entity_id' => 2,
        'boolean_value' => true,
    ]);
    expect($boolValue->boolean_value)->toBeTrue();

    // Test integer value
    $intValue = CustomFieldValue::create([
        'custom_field_id' => $customField->id,
        'entity_type' => 'App\\Models\\User',
        'entity_id' => 3,
        'integer_value' => 42,
    ]);
    expect($intValue->integer_value)->toBe(42);

    // Test float value
    $floatValue = CustomFieldValue::create([
        'custom_field_id' => $customField->id,
        'entity_type' => 'App\\Models\\User',
        'entity_id' => 4,
        'float_value' => 99.99,
    ]);
    expect($floatValue->float_value)->toBe(99.99);
});

it('can store date values', function () {
    $customField = CustomField::create([
        'name' => 'Date Field',
        'code' => 'date_field',
        'type' => CustomFieldType::DATE,
        'entity_type' => 'App\\Models\\User',
        'active' => true,
    ]);

    $date = Carbon::now()->startOfDay();
    
    $value = CustomFieldValue::create([
        'custom_field_id' => $customField->id,
        'entity_type' => 'App\\Models\\User',
        'entity_id' => 1,
        'date_value' => $date,
    ]);

    expect($value->date_value)->toBeInstanceOf(Carbon::class);
    expect($date->equalTo($value->date_value))->toBeTrue();
});

it('uses custom table name from config', function () {
    config(['custom-fields.table_names.custom_field_values' => 'my_custom_field_values']);
    
    $value = new CustomFieldValue();
    
    expect($value->getTable())->toBe('my_custom_field_values');
});

it('has no timestamps', function () {
    $value = new CustomFieldValue();
    
    expect($value->timestamps)->toBeFalse();
}); 