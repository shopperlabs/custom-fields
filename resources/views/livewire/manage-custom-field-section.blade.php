<x-filament::section x-sortable-item="{{ $section['id'] }}" compact>
    <x-slot name="heading">
        <div class="flex justify-between">

            <div class="flex items-center gap-x-1">

                <div class="border-r">
                    <x-filament::icon-button
                        icon="heroicon-m-bars-4"
                        color="gray"
                        x-sortable-handle
                    />
                </div>


                {{$section['name']}}
            </div>

            <div class="flex items-center gap-x-1">
                {{ $this->editAction }}
                {{ $this->deleteAction }}
            </div>
        </div>
    </x-slot>


    <div
        x-sortable
        x-sortable-group="fields"
        data-section-id="{{ $section['id'] }}"
        x-on:end.stop="$wire.updateFieldsOrder($event.to.getAttribute('data-section-id'), $event.to.sortable.toArray()) && $wire.$refresh()"
        class="gap-3 grid grid-cols-12"
    >
        @foreach ($this->fields as $field)
            @livewire('manage-custom-field', ['field' => $field], key($field['id']))
        @endforeach

        @if(!count($this->fields))
            <div class="col-span-12">
                <div
                    class="flex justify-center items-center py-3 border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                    <span class="text-gray-500 dark:text-gray-400 text-sm">Add or drag fields here</span>
                </div>
            </div>
        @endempty
    </div>

    <x-slot name="footerActions">
        <x-filament::button size="sm" wire:click="mountAction('createFieldAction', { sectionId: 12345 })">
            Create field
        </x-filament::button>
    </x-slot>

    <x-filament-actions::modals/>

</x-filament::section>
