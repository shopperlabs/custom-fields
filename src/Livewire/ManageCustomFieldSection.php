<?php

namespace Relaticle\CustomFields\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\ActionSize;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Relaticle\CustomFields\Filament\FormSchemas\FieldForm;
use Relaticle\CustomFields\Filament\FormSchemas\SectionForm;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Models\CustomFieldSection;
use Relaticle\CustomFields\Support\Utils;

class ManageCustomFieldSection extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public string $entityType;
    public CustomFieldSection $section;

    #[Computed]
    public function fields()
    {
        return $this->section->fields()->orderBy('sort_order')->get();
    }

    #[On('field-width-updated')]
    public function fieldWidthUpdated(int $fieldId, int $width): void
    {
        // Update the width
        CustomField::where('id', $fieldId)->update(['width' => $width]);

        // Re-fetch the fields
        $this->section->refresh();
    }

    public function updateFieldsOrder($sectionId, $fields): void
    {
        foreach ($fields as $index => $field) {
            CustomField::query()
                ->where('id', $field)
                ->update([
                    'custom_field_section_id' => $sectionId,
                    'sort_order' => $index,
                ]);
        }
    }

    public function createFieldAction(): Action
    {
        return Action::make('createField')
            ->size(ActionSize::ExtraSmall)
            ->label(__('custom-fields::custom-fields.field.form.add_field'))
            ->model(CustomField::class)
            ->form(FieldForm::schema())
            ->fillForm([
                'entity_type' => $this->entityType,
            ])
            ->mutateFormDataUsing(function (array $data): array {
                if (Utils::isTenantEnabled()) {
                    $data[config('custom-fields.column_names.tenant_foreign_key')] = Filament::getTenant()?->id;
                }

                $data['custom_field_section_id'] = $this->section->id;

                return $data;
            })
            ->action(fn(array $data) => CustomField::create($data))
            ->slideOver();
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->iconButton()
            ->icon('heroicon-m-pencil')
            ->color('gray')
            ->model(CustomFieldSection::class)
            ->record($this->section)
            ->form(SectionForm::schema())
            ->fillForm($this->section->toArray())
            ->action(fn(array $data) => $this->section->update($data))
            ->modalWidth('max-w-2xl');
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->requiresConfirmation()
            ->iconButton()
            ->icon('heroicon-m-trash')
            ->color('gray')
            ->action(fn() => $this->section->delete());
    }

    public function render()
    {
        return view('custom-fields::livewire.manage-custom-field-section');
    }
}
