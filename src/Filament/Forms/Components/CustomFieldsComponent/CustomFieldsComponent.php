<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Field;
use Illuminate\Support\Collection;
use ManukMinasyan\FilamentCustomField\Models\CustomField;
use ManukMinasyan\FilamentCustomField\Services\EntityTypeOptionsService;

final class CustomFieldsComponent extends Component
{
    protected string $view = 'filament-forms::components.group';

    public function __construct(private readonly CustomFieldComponentFactory $componentFactory)
    {
        // Defer schema generation until we can safely access the record
        $this->schema(fn () => $this->generateSchema());
    }

    public static function make(): static
    {
        return app(self::class);
    }

    /**
     * @return array<int, Field>
     */
    protected function generateSchema(): array
    {
        return $this->getCustomFields()
            ->map(fn (CustomField $customField): Field => $this->componentFactory->create($customField))
            ->toArray();
    }

    /**
     * @return Collection<int, CustomField>
     */
    protected function getCustomFields(): Collection
    {
        return CustomField::query()
            ->with(['options'])
            ->forEntity($this->getModel())
            ->get();
    }
}
