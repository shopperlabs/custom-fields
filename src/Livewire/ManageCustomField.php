<?php

namespace Relaticle\CustomFields\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;
use Relaticle\CustomFields\Models\CustomField;

class ManageCustomField extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public CustomField $field;

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->requiresConfirmation()
            ->action(fn () => $this->field->delete());
    }

    public function render()
    {
        return view('custom-fields::livewire.manage-custom-field');
    }
}
