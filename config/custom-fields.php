<?php

return [
    'allowed_resources' => [
        \App\Filament\Resources\UserResource::class,
    ],

    'disallowed_resources' => [
        \App\Filament\Resources\CompanyResource::class,
    ],

    'table_names' => [
        'attributes' => 'attributes',
        'attribute_values' => 'attribute_values',
        'attribute_options' => 'attribute_options',
    ],
];
