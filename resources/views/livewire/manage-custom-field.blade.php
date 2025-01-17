<x-filament::grid.column default="{{ $field->width->getSpanValue() }}" x-sortable-item="{{ $field->id }}">
    <x-filament::section
        compact
    >
        <div class="flex justify-between">
            <div class="flex items-center gap-x-2 w-full" x-sortable-handle>
                <x-filament::icon-button
                    icon="heroicon-m-bars-4"
                    color="gray"
                />

                <x-filament::icon
                    :icon="$field->type->getIcon()"
                    class="h-5 w-5 text-gray-500 dark:text-gray-400"
                    :aria-label="$field->name"
                />

                <span>{{ $field->name }}</span>
            </div>

            <div class="flex items-center gap-x-1 px-2 py-0.5">

                <livewire:manage-custom-field-width
                    :selected-width="$field->width"
                    :field-id="$field->id"
                    wire:key="manage-custom-field-width-{{ $field->id }}"
                />

                {{ $this->editAction() }}
                {{ $this->deleteAction() }}
            </div>
        </div>

    </x-filament::section>

    <x-filament-actions::modals/>

</x-filament::grid.column>
