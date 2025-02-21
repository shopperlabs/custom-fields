<?php

namespace Relaticle\CustomFields\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CustomFieldSettingsData extends Data
{
    public function __construct(
        public bool $visible_in_list = true,
        public bool $visible_in_view = true,
        public bool $searchable = false,
        public bool $encrypted = false,
    )
    {
    }
}
