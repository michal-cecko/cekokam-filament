<?php

namespace App\Forms\Components;

use App\Enum\RoleEnum;
use Filament\Forms\Components\Field;

class PhoneInput extends Field
{
    protected string $view = 'forms.components.phone-input';

    public function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(function (self $component, $state) {
            if (is_null($state)) {
                $component->state([]);
            } elseif (is_string($state)) {
                $component->state(json_decode($state, true) ?? []);
            }
        });

        $this->disabled(auth()->user()->role !== RoleEnum::ADMIN);

        $this->dehydrateStateUsing(function ($state) {
            if (empty($state)) {
                return [];
            }

            return array_values(array_filter($state));
        });

        $this->reactive();
    }
}
