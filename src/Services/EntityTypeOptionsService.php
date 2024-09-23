<?php

namespace Relaticle\CustomFields\Services;

class EntityTypeOptionsService extends AbstractOptionsService
{
    protected static string $allowedConfigKey = 'custom-fields.allowed_entity_resources';
    protected static string $disallowedConfigKey = 'custom-fields.disallowed_entity_resources';
}
