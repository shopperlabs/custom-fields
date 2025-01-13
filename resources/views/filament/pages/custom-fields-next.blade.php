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
                            <x-filament::icon-button
                                color="gray"
                                icon="heroicon-m-pencil"
                                wire:click="editSection({{ $section['id'] }})"
                                label="Edit section"
                            />
                            <x-filament::icon-button
                                color="gray"
                                icon="heroicon-m-trash"
                                wire:click="deleteSection({{ $section['id'] }})"
                                label="Delete section"
                            />
                        </div>
                    </div>
                </x-slot>


                <div
                    x-sortable
                    x-sortable-group="fields"
                    data-section-id="{{ $section['id'] }}"
                    x-on:end.stop="$wire.updateFieldsOrder($event.to.getAttribute('data-section-id'), $event.to.sortable.toArray())"
                    class="gap-3 grid grid-cols-12"
                >
                    @foreach ($section['fields'] as $field)
                        <x-filament::section
                            compact
                            x-sortable-item="{{ $field['id'] }}"
                            class="{{ $field['col_span_class'] }} }}">
                            <div class="flex justify-between">
                                <div class="flex items-center gap-x-2">
                                    <div class="border-r py-0.5">
                                        <x-filament::icon-button
                                            icon="heroicon-m-bars-4"
                                            color="gray"
                                            x-sortable-handle
                                        />
                                    </div>


                                    <x-filament::icon
                                        :icon="$field['type']->getIcon()"
                                        class="h-5 w-5 text-gray-500 dark:text-gray-400"
                                        :aria-label="$field['name']"
                                    />

                                    <x-filament::link :href="'#'">
                                        {{ $field['name'] }}
                                    </x-filament::link>
                                </div>

                                <div class="flex items-center gap-x-1 px-2 py-0.5">

                                    <livewire:width-selector
                                        :selected-width="$field['width']"
                                        :field-id="$field['id']"
                                        wire:key="width-selector-{{ $field['id'] }}"
                                    />

                                    <x-filament::icon-button
                                        color="gray"
                                        size="xs"
                                        icon="heroicon-m-trash"
                                        wire:click="openNewUserModal"
                                        label="New label"
                                    />
                                </div>
                            </div>
                        </x-filament::section>
                    @endforeach
                    @if(!count($section['fields']))
                        <div class="col-span-12">
                            <div class="flex justify-center items-center py-3 border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
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
            </x-filament::section>
        @endforeach

        {{ $this->createSectionAction }}

    </div>
</x-filament-panels::page>
