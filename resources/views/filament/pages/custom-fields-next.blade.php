<x-filament-panels::page>
    <x-filament::tabs label="Content tabs" contained>
        @foreach ($this->entityTypes as $key => $label)
            <x-filament::tabs.item active="{{ $key === $this->currentEntityType }}"
                                   wire:click="setCurrentEntityType('{{ $key }}')">
                {{ $label }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>


    <div
        x-sortable
        wire:end.stop="updateSectionsOrder($event.target.sortable.toArray())"
        class="flex flex-col gap-y-6"
    >
        @foreach ($this->sections as $section)
            @livewire('manage-custom-field-section', ['entityType' => $this->currentEntityType, 'section' => $section], key($section->id . str()->random(16)))
        @endforeach

        {{ $this->createSectionAction }}
    </div>


</x-filament-panels::page>
