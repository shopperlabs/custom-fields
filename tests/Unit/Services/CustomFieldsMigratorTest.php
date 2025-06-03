<?php

declare(strict_types=1);

use Relaticle\CustomFields\Exceptions\CustomFieldDoesNotExistException;
use Relaticle\CustomFields\Migrations\CustomFieldsMigrator;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Enums\CustomFieldType;

it('can create a migrator instance', function () {
    $migrator = app(CustomFieldsMigrator::class);
    
    expect($migrator)->toBeInstanceOf(CustomFieldsMigrator::class);
});

it('can update an existing field directly', function () {
    // Create a field first
    $customField = CustomField::create([
        'name' => 'Original Name',
        'code' => 'test_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
        'active' => true,
        'settings' => json_encode(['encrypted' => false]),
    ]);

    // Update the field directly
    $customField->update(['name' => 'Updated Name']);

    $updatedField = $customField->fresh();
    expect($updatedField->name)->toBe('Updated Name');
});

it('can delete an existing field', function () {
    // Create a field first
    $customField = CustomField::create([
        'name' => 'Field to Delete',
        'code' => 'field_to_delete',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
        'active' => true,
        'settings' => json_encode(['encrypted' => false]),
    ]);

    $fieldId = $customField->id;
    
    // Delete the field
    $customField->delete();

    expect(CustomField::find($fieldId))->toBeNull();
});

it('can activate and deactivate a field', function () {
    // Create an inactive field
    $customField = CustomField::create([
        'name' => 'Toggle Field',
        'code' => 'toggle_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
        'active' => false,
        'settings' => json_encode(['encrypted' => false]),
    ]);

    // Activate the field
    $customField->update(['active' => true]);
    expect($customField->fresh()->active)->toBeTruthy();
    
    // Deactivate the field
    $customField->update(['active' => false]);
    expect($customField->fresh()->active)->toBeFalsy();
}); 