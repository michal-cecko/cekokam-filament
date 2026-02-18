<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <textarea
        {{ $attributes->merge(['class' => 'filament-forms-textarea-component w-full rounded-md shadow-sm border-gray-300 focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white']) }}
        {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}"
    ></textarea>
    <div class="text-sm text-gray-500 dark:text-gray-400">
        <span>{{ $getCharacterCount() }}</span>
        <span class="ml-2">{{ $getDiacritics() ? "S diakritikou" : "Bez diakritiky" }}</span>
        <span class="ml-2">{{ $getSmsCount() }}</span>
    </div>
</x-dynamic-component>
