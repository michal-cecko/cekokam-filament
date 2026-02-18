<x-filament-panels::page class="service-prices-page" style="max-width: 37rem;">
    <div class="rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <table class="w-full table-auto divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3 text-start text-sm font-semibold text-gray-950 dark:text-white border-e border-gray-200 dark:border-white/10">
                        Počet TV
                    </th>

                    @foreach($serviceTypes as $type)
                        <th class="px-4 py-3 text-center text-sm font-semibold text-gray-950 dark:text-white relative border-e border-gray-200 dark:border-white/10 last:border-e-0">
                            {{ $type->name }}
                            <x-heroicon-m-trash
                                wire:click="$dispatch('open-modal', { id: 'delete-service-type-{{ $type->id }}' })"
                                class="w-4 h-4 absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-danger-500 dark:text-gray-500 dark:hover:text-danger-400 transition-colors cursor-pointer"
                            />

                            <x-filament::modal
                                id="delete-service-type-{{ $type->id }}"
                                icon="heroicon-o-exclamation-triangle"
                                icon-color="danger"
                                heading="Zmazať službu {{ $type->name }}"
                                width="md"
                            >
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Naozaj chcete vymazať službu {{ $type->name }}? Akciu nieje možné zvrátiť. Odstráni to aj služby zákazníkov, ktoré majú priradenú túto službu.
                                </p>

                                <x-slot name="footerActions">
                                    <x-filament::button
                                        color="gray"
                                        wire:click="$dispatch('close-modal', { id: 'delete-service-type-{{ $type->id }}' })"
                                    >
                                        Nie, odísť
                                    </x-filament::button>

                                    <x-filament::button
                                        color="danger"
                                        wire:click="removeServiceType({{ $type->id }})"
                                    >
                                        Áno, vymazať
                                    </x-filament::button>
                                </x-slot>
                            </x-filament::modal>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                @foreach($serviceCounts as $count)
                    <tr class="{{ $loop->even ? 'bg-gray-50 dark:bg-white/5' : 'bg-white dark:bg-gray-900' }}">
                        <td class="px-4 py-3 text-sm text-gray-950 dark:text-white relative whitespace-nowrap border-e border-gray-200 dark:border-white/10">
                            <x-heroicon-m-trash
                                wire:click="$dispatch('open-modal', { id: 'delete-service-count-{{ $count->count_value }}' })"
                                class="w-4 h-4 absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-danger-500 dark:text-gray-500 dark:hover:text-danger-400 transition-colors cursor-pointer"
                            />

                            <x-filament::modal
                                id="delete-service-count-{{ $count->count_value }}"
                                icon="heroicon-o-exclamation-triangle"
                                icon-color="danger"
                                heading="Zmazať počet TV: {{ $count->count_value }}"
                                width="md"
                            >
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Naozaj chcete vymazať počet TV {{ $count->count_value }}? Akciu nieje možné zvrátiť. Odstráni to aj služby zákazníkov, ktoré majú priradený tento počet.
                                </p>

                                <x-slot name="footerActions">
                                    <x-filament::button
                                        color="gray"
                                        wire:click="$dispatch('close-modal', { id: 'delete-service-count-{{ $count->count_value }}' })"
                                    >
                                        Nie, odísť
                                    </x-filament::button>

                                    <x-filament::button
                                        color="danger"
                                        wire:click="removeServiceCount({{ $count->count_value }})"
                                    >
                                        Áno, vymazať
                                    </x-filament::button>
                                </x-slot>
                            </x-filament::modal>

                            <span>{{ $count->count_value }}</span>
                        </td>

                        @foreach($serviceTypes as $type)
                            <td class="px-4 py-3 text-center border-e border-gray-200 dark:border-white/10 last:border-e-0">
                                <x-filament::input.wrapper>
                                    <x-filament::input
                                        type="number"
                                        wire:model="prices.{{ $count->count_value }}.{{ $type->id }}"
                                        wire:change="updatePrice({{ $count->count_value }}, {{ $type->id }}, $event.target.value)"
                                        class="w-24 text-left"
                                        step="0.01"
                                        min="0"
                                    />
                                </x-filament::input.wrapper>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
