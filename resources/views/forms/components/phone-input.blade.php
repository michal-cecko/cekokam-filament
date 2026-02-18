@php
    $id = $getId();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :hint="$getHint()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <div
        x-data="{
            state: $wire.entangle('{{ $getStatePath() }}'),
            newPhone: '',
            phonePattern: new RegExp('^(\\+421|0|\\+420)\\d{9}$'),

            init() {
                if (!Array.isArray(this.state)) {
                    this.state = [];
                }
            },

            validatePhone(phone) {
                return true;
                return this.phonePattern.test(phone.trim());
            },

            addPhone() {
                const phone = this.newPhone.trim();
                if (phone && this.validatePhone(phone)) {
                    this.state = [...(this.state || []), phone];
                    this.newPhone = '';
                }
            },

            removePhone(index) {
                this.state = this.state.filter((_, i) => i !== index);
            }
        }"
        class="custom-phone-input"
    >
        <div class="custom-wrapper">
            <div class="fi-input-wrp w-full flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 focus-within:ring-primary-600 dark:focus-within:ring-primary-500 fi-fo-text-input overflow-hidden">
                <div class="min-w-0 flex-1">
                    <input type="tel" x-model="newPhone" @keydown.enter.prevent="addPhone()" class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 dark:text-white dark:placeholder:text-gray-500 sm:text-sm sm:leading-6 bg-white/0 ps-3 pe-3" placeholder="Tel...">
                </div>
                <button type="button" @click="addPhone()" style="max-width: 48px" class="flex items-center justify-center pe-3 ps-3 border-s border-gray-200 dark:border-white/10 text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 transition duration-75" title="Add phone">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"></path>
                    </svg>
                </button>
            </div>
        </div>
        {{--<div x-show="newPhone && !phonePattern.test(newPhone)" class="text-xs text-danger-600 mt-1">(+421|+420|09 a 9 číslic)</div>--}}
        <div class="space-y-2 added-phones mt-2">
            <template x-for="(phone, index) in state" :key="index">
                <div class="flex items-center justify-between gap-2 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <span x-text="phone" class="added-phone"></span>
                    <button type="button" @click="removePhone(index)" class="text-danger-600 hover:text-danger-500">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>
</x-dynamic-component>
