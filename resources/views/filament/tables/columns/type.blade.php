<span class="fi-ta-text-item-label flex items-center text-sm leading-6 text-gray-950 dark:text-white">
    <!-- Enhanced Accessibility with aria-label attribute for icon -->
    <x-filament::icon
        :icon="$getState()->getIcon()"
        class="h-5 w-5 text-gray-500 dark:text-gray-400"
        :aria-label="$getState()->getLabel()"
    />
    <!-- Label now wrapped for dynamic state, ensures safe HTML rendering if necessary-->
    <span class="ml-2">{{ $getState()->getLabel() }}</span>
</span>
