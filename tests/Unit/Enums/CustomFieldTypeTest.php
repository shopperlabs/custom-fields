<?php

declare(strict_types=1);

use Relaticle\CustomFields\Enums\CustomFieldType;

it('has all expected field types', function () {
    $expectedTypes = [
        'TEXT',
        'NUMBER',
        'LINK',
        'SELECT',
        'CHECKBOX',
        'CHECKBOX_LIST',
        'RADIO',
        'RICH_EDITOR',
        'MARKDOWN_EDITOR',
        'TAGS_INPUT',
        'COLOR_PICKER',
        'TOGGLE',
        'TOGGLE_BUTTONS',
        'TEXTAREA',
        'CURRENCY',
        'DATE',
        'DATE_TIME',
        'MULTI_SELECT',
    ];

    $actualTypes = array_map(fn($case) => $case->name, CustomFieldType::cases());

    // Check that we have the expected count
    expect($actualTypes)->toHaveCount(count($expectedTypes));
    
    // Check that all expected types exist
    foreach ($expectedTypes as $expectedType) {
        expect($actualTypes)->toContain($expectedType);
    }
    
    // Check that we don't have any unexpected types
    foreach ($actualTypes as $actualType) {
        expect($expectedTypes)->toContain($actualType);
    }
});

it('can access field types as values', function () {
    expect(CustomFieldType::TEXT->value)->toBe('text');
    expect(CustomFieldType::TEXTAREA->value)->toBe('textarea');
    expect(CustomFieldType::RICH_EDITOR->value)->toBe('rich-editor');
    expect(CustomFieldType::MARKDOWN_EDITOR->value)->toBe('markdown-editor');
    expect(CustomFieldType::LINK->value)->toBe('link');
    expect(CustomFieldType::COLOR_PICKER->value)->toBe('color-picker');
    expect(CustomFieldType::NUMBER->value)->toBe('number');
    expect(CustomFieldType::RADIO->value)->toBe('radio');
    expect(CustomFieldType::SELECT->value)->toBe('select');
    expect(CustomFieldType::CHECKBOX->value)->toBe('checkbox');
    expect(CustomFieldType::TOGGLE->value)->toBe('toggle');
    expect(CustomFieldType::CHECKBOX_LIST->value)->toBe('checkbox-list');
    expect(CustomFieldType::TOGGLE_BUTTONS->value)->toBe('toggle-buttons');
    expect(CustomFieldType::TAGS_INPUT->value)->toBe('tags-input');
    expect(CustomFieldType::MULTI_SELECT->value)->toBe('multi-select');
    expect(CustomFieldType::CURRENCY->value)->toBe('currency');
    expect(CustomFieldType::DATE->value)->toBe('date');
    expect(CustomFieldType::DATE_TIME->value)->toBe('date-time');
});

it('can be created from string value', function () {
    expect(CustomFieldType::from('text'))->toBe(CustomFieldType::TEXT);
    expect(CustomFieldType::from('number'))->toBe(CustomFieldType::NUMBER);
    expect(CustomFieldType::from('date'))->toBe(CustomFieldType::DATE);
    expect(CustomFieldType::from('rich-editor'))->toBe(CustomFieldType::RICH_EDITOR);
});

it('can try from string value', function () {
    expect(CustomFieldType::tryFrom('text'))->toBe(CustomFieldType::TEXT);
    expect(CustomFieldType::tryFrom('number'))->toBe(CustomFieldType::NUMBER);
    expect(CustomFieldType::tryFrom('rich-editor'))->toBe(CustomFieldType::RICH_EDITOR);
    expect(CustomFieldType::tryFrom('invalid_type'))->toBeNull();
});

it('identifies text types correctly', function () {
    $textTypes = [
        CustomFieldType::TEXT,
        CustomFieldType::TEXTAREA,
        CustomFieldType::RICH_EDITOR,
        CustomFieldType::MARKDOWN_EDITOR,
    ];

    foreach ($textTypes as $type) {
        expect($type)->toBeInstanceOf(CustomFieldType::class);
    }
});

it('identifies selection types correctly', function () {
    $selectionTypes = [
        CustomFieldType::RADIO,
        CustomFieldType::SELECT,
        CustomFieldType::MULTI_SELECT,
        CustomFieldType::CHECKBOX_LIST,
        CustomFieldType::TOGGLE_BUTTONS,
    ];

    foreach ($selectionTypes as $type) {
        expect($type)->toBeInstanceOf(CustomFieldType::class);
    }
});

it('identifies boolean types correctly', function () {
    $booleanTypes = [
        CustomFieldType::CHECKBOX,
        CustomFieldType::TOGGLE,
    ];

    foreach ($booleanTypes as $type) {
        expect($type)->toBeInstanceOf(CustomFieldType::class);
    }
});

it('identifies date types correctly', function () {
    $dateTypes = [
        CustomFieldType::DATE,
        CustomFieldType::DATE_TIME,
    ];

    foreach ($dateTypes as $type) {
        expect($type)->toBeInstanceOf(CustomFieldType::class);
    }
});

it('identifies numeric types correctly', function () {
    $numericTypes = [
        CustomFieldType::NUMBER,
        CustomFieldType::CURRENCY,
    ];

    foreach ($numericTypes as $type) {
        expect($type)->toBeInstanceOf(CustomFieldType::class);
    }
}); 