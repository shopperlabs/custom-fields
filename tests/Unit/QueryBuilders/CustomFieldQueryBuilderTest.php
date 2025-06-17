<?php

declare(strict_types=1);

use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Models\CustomField;

it('can filter by field type', function () {
    $textField = CustomField::create([
        'name' => 'Text Field',
        'code' => 'text_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
    ]);

    $numberField = CustomField::create([
        'name' => 'Number Field',
        'code' => 'number_field',
        'type' => CustomFieldType::NUMBER,
        'entity_type' => 'App\\Models\\User',
    ]);

    // Check that records were created
    $allFields = CustomField::withoutGlobalScopes()->get();
    expect($allFields)->toHaveCount(2);

    // Test the query builder methods
    $textFields = CustomField::withoutGlobalScopes()->forType(CustomFieldType::TEXT)->get();
    $numberFields = CustomField::withoutGlobalScopes()->forType(CustomFieldType::NUMBER)->get();

    expect($textFields)->toHaveCount(1);
    expect($numberFields)->toHaveCount(1);
    expect($textFields->first()->id)->toBe($textField->id);
    expect($numberFields->first()->id)->toBe($numberField->id);
});

it('can filter by entity class', function () {
    $userField = CustomField::create([
        'name' => 'User Field',
        'code' => 'user_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\User',
    ]);

    $postField = CustomField::create([
        'name' => 'Post Field',
        'code' => 'post_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'App\\Models\\Post',
    ]);

    // Check that records were created
    $allFields = CustomField::withoutGlobalScopes()->get();
    expect($allFields)->toHaveCount(2);

    $userFields = CustomField::withoutGlobalScopes()->forEntity('App\\Models\\User')->get();
    $postFields = CustomField::withoutGlobalScopes()->forEntity('App\\Models\\Post')->get();

    expect($userFields)->toHaveCount(1);
    expect($postFields)->toHaveCount(1);
    expect($userFields->first()->id)->toBe($userField->id);
    expect($postFields->first()->id)->toBe($postField->id);
});

it('can filter by morph entity', function () {
    $userField = CustomField::create([
        'name' => 'User Field',
        'code' => 'user_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'user',
    ]);

    $postField = CustomField::create([
        'name' => 'Post Field',
        'code' => 'post_field',
        'type' => CustomFieldType::TEXT,
        'entity_type' => 'post',
    ]);

    // Check that records were created
    $allFields = CustomField::withoutGlobalScopes()->get();
    expect($allFields)->toHaveCount(2);

    $userFields = CustomField::withoutGlobalScopes()->forMorphEntity('user')->get();
    $postFields = CustomField::withoutGlobalScopes()->forMorphEntity('post')->get();

    expect($userFields)->toHaveCount(1);
    expect($postFields)->toHaveCount(1);
    expect($userFields->first()->id)->toBe($userField->id);
    expect($postFields->first()->id)->toBe($postField->id);
});
