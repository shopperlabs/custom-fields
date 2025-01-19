<?php

namespace Relaticle\CustomFields\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\ActionSize;
use Illuminate\View\View;
use Livewire\Component;
use Relaticle\CustomFields\Filament\FormSchemas\FieldForm;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Support\Utils;

class ManageCustomField extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithRecord;

    public CustomField $field;

    public function editAction(): Action
    {
        return Action::make('edit')
            ->size(ActionSize::ExtraSmall)
            ->iconButton()
            ->icon('heroicon-o-pencil')
            ->color('gray')
            ->model(CustomField::class)
            ->record($this->field)
            ->form(FieldForm::schema())
            ->fillForm($this->field->toArray())
            ->mutateFormDataUsing(function (array $data): array {
                if (Utils::isTenantEnabled()) {
                    $data[config('custom-fields.column_names.tenant_foreign_key')] = Filament::getTenant()?->id;
                }

                return $data;
            })
            ->action(fn(array $data) => $this->field->update($data))
            ->slideOver();
    }


    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->requiresConfirmation()
            ->iconButton()
            ->icon('heroicon-o-trash')
            ->size(ActionSize::ExtraSmall)
            ->color('gray')
            ->action(fn () => $this->field->delete());
    }

    public function setWidth(int $fieldId, int $width): void
    {
        $this->dispatch('field-width-updated', $fieldId, $width);
    }

    public function render(): View
    {
        return view('custom-fields::livewire.manage-custom-field');
    }
}
