{{-- resources/views/tables/customers/services-info-modal.blade.php --}}
<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse ($record->services as $service)
            <x-filament::card>
                <div class="space-y-2">
                    {{-- Service Type Header --}}
                    <div class="text-lg flex items-center justify-between text-gray-900">
                        <h3 class="font-bold">
                            {{ $service->full_service_name }}
                        </h3>

                        {{-- Price --}}
                        <div class="flex justify-between text-sm">
                            {{ $service->price }}€ <span>/ mes</span>
                        </div>
                    </div>

                    <div class="space-y-1">
                        {{-- Subscription Period --}}
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:text-sm w-full gap-3">
                                <span class="text-gray-500 sm:w-auto w-full">Platnosť</span>
                                <span class="font-medium sm:w-auto w-full text-right">
                                    {{ $service->subscription_start?->translatedFormat('F Y') }} - {{ ($service->subscription_end?->translatedFormat('F Y') ?? "Stále") }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-filament::card>
        @empty
            <div class="col-span-full">
                <x-filament::card>
                    <div class="flex items-center justify-center h-24 text-gray-500">
                        Zákazník nemá žiadne aktívne služby.
                    </div>
                </x-filament::card>
            </div>
        @endforelse
    </div>
</div>
