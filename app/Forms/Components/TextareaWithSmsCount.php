<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Str;

class TextareaWithSmsCount extends Field
{
    protected string $view = 'forms.components.textarea-with-sms-count';

    public function getCharacterCount(): string
    {
        $value = $this->getState();
        $characterCount = Str::length($value);

        return "$characterCount znakov";
    }

    public function getSmsCount(): string
    {
        $value = $this->getState();
        $containsDiacritics = $this->getDiacritics();
        $characterCount = Str::length($value);
        $smsCount = $containsDiacritics ? ceil($characterCount / 67) : ceil($characterCount / 160);

        return "$smsCount SMS";
    }

    public function getDiacritics(): bool
    {
        return preg_match('/[^\x00-\x7F]/', $this->getState()) > 0;
    }
}
