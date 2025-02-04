<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\FormSchemas;

interface SectionFormInterface
{
    public static function entityType(string $entityType): self;
}
