<?php

namespace App\Filament\Pages;

use App\Enum\RoleEnum;
use App\Models\Setting;
use App\Services\Customers\CustomerSmsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class AppSettings extends Page implements HasActions, HasForms
{
    use InteractsWithActions, InteractsWithForms;

    protected static ?string $title = 'Nastavenia';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Nastavenia';

    protected static ?string $slug = 'settings';

    protected string $view = 'filament.pages.app-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()->role === RoleEnum::ADMIN;
    }

    public function mount(): void
    {
        $this->form->fill([
            CustomerSmsService::PAYMENT_REQUEST_TEMPLATE_KEY => Setting::get(
                CustomerSmsService::PAYMENT_REQUEST_TEMPLATE_KEY,
                CustomerSmsService::DEFAULT_PAYMENT_REQUEST_TEMPLATE,
            ),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                Section::make('SMS - Výzva k platbe')
                    ->description('Šablóna SMS správy, ktorá sa odošle zákazníkom pri výzve k platbe. Použiteľné premenné sú uvedené nižšie.')
                    ->schema([
                        Textarea::make(CustomerSmsService::PAYMENT_REQUEST_TEMPLATE_KEY)
                            ->label('Obsah SMS')
                            ->rows(14)
                            ->required()
                            ->helperText('Premenné v zložených zátvorkách (napr. {name}) sa pri odoslaní automaticky nahradia hodnotami z karty zákazníka.'),

                        Placeholder::make('variables')
                            ->label('Dostupné premenné')
                            ->content(fn (): HtmlString => new HtmlString(self::variableListHtml())),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::set(
            CustomerSmsService::PAYMENT_REQUEST_TEMPLATE_KEY,
            (string) ($state[CustomerSmsService::PAYMENT_REQUEST_TEMPLATE_KEY] ?? ''),
        );

        Notification::make()
            ->title('Nastavenia uložené')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Uložiť')
                ->icon('heroicon-m-check')
                ->action('save'),
        ];
    }

    private static function variableListHtml(): string
    {
        $rows = '';
        foreach (CustomerSmsService::paymentRequestVariables() as $token => $label) {
            $rows .= '<li><code class="font-mono text-primary-600">'.e($token).'</code> — '.e($label).'</li>';
        }

        return '<ul class="list-disc ml-6 space-y-1 text-sm">'.$rows.'</ul>';
    }
}
