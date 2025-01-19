<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Forms\Components;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Field;
use Illuminate\Support\Collection;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\FieldComponentFactory;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent\SectionComponentFactory;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Models\CustomFieldSection;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;

final class CustomFieldsComponent extends Component
{
    protected string $view = 'filament-forms::components.group';

    public function __construct(
        private readonly SectionComponentFactory $sectionComponentFactory,
        private readonly FieldComponentFactory   $fieldComponentFactory
    )
    {
        // Defer schema generation until we can safely access the record
        $this->schema(fn() => $this->generateSchema());
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
//        dd(
//            $this->getModel(),
//            CustomFieldSection::query()
//                ->with('fields')
//                ->forEntityType($this->getModel())
//                ->get()
//                ->map(function (CustomFieldSection $section) {
//                    return $this->sectionComponentFactory->create($section)->schema(
//                        function () use ($section) {
//                            return $section->fields->map(function (CustomField $customField) {
//                                return $this->fieldComponentFactory->create($customField);
//                            })->toArray();
//                        }
//                    );
//                })
//        );
        return CustomFieldSection::query()
            ->with('fields')
            ->forEntityType($this->getModel())
            ->orderBy('sort_order')
            ->get()
            ->map(function (CustomFieldSection $section) {
                return $this->sectionComponentFactory->create($section)->schema(
                    function () use ($section) {
                        return $section->fields->map(function (CustomField $customField) {
                            return $this->fieldComponentFactory->create($customField);
                        })->toArray();
                    }
                );
            })
            ->toArray();
    }

    /**
     * @return Collection<int, CustomField>
     */
    protected function getCustomFields(): Collection
    {
        return CustomField::query()
            ->with(['section', 'options'])
            ->forEntity($this->getModel())
            ->get();
    }
}
