<?php

return [
    'nav' => [
        'label' => 'Custom Fields',
        'group' => 'Custom Fields',
        'icon' => 'heroicon-o-cube'
    ],

    'section' => [
        'form' => [
            'name' => 'Name',
            'code' => 'Code',
            'type' => 'Type',
            'description' => 'Description',
        ]
    ],

    'field' => [
        'form' => [
            'general' => 'General',
            'entity_type' => 'Entity Type',
            'type' => 'Type',
            'name' => 'Name',
            'name_helper_text' => "The field's label shown in the table's and form's.",
            'code' => 'Code',
            'code_helper_text' => 'Unique code to identify this field throughout the resource.',
            'options_lookup_type' => [
                'label' => 'Options Lookup Type',
                'options' => 'Options',
                'lookup' => 'Lookup',
            ],
            'lookup_type' => [
                'label' => 'Lookup Type',
            ],
            'options' => [
                'label' => 'Options',
                'add' => 'Add Option',
            ],
            'validation' => [
                'label' => 'Validation',
            ]
        ]
    ],
];
