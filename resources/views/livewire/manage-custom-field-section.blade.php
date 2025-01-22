<x-filament::section x-sortable-item="{{ $section->id }}" compact>
    <x-slot name="heading">
        <div class="flex justify-between">
            <div class="flex items-center gap-x-1">
                <x-filament::icon-button
                    icon="heroicon-m-bars-4"
                    color="gray"
                    x-sortable-handle
                />

                {{$section->name }}
            </div>

            <div class="flex items-center gap-x-1">
                {{ $this->editAction }}
                {{ $this->deleteAction }}
            </div>
        </div>
    </x-slot>


    <x-filament::grid
        x-sortable
        x-sortable-group="fields"
        data-section-id="{{ $section->id }}"
        default="12"
        class="gap-4"
        @end.stop="$wire.updateFieldsOrder($event.to.getAttribute('data-section-id'), $event.to.sortable.toArray())"
    >
        @foreach ($this->fields as $field)
                @livewire('manage-custom-field', ['field' => $field], key($field->id . $field->width->value . str()->random(16)))
        @endforeach

        @if(!count($this->fields))
            <div class="col-span-12">
                <div
                    class="flex justify-center items-center py-3 border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                    <span class="text-gray-500 dark:text-gray-400 text-sm">Add or drag fields here</span>
                </div>
            </div>
        @endempty
    </x-filament::grid>

    <x-slot name="footerActions">
        {{ $this->createFieldAction() }}
    </x-slot>

    <x-filament-actions::modals/>

</x-filament::section>
