<?php

namespace App\Filament\Resources\ChannelStreams;

use App\Enum\RoleEnum;
use App\Filament\Resources\ChannelStreams\Pages\CreateChannelStream;
use App\Filament\Resources\ChannelStreams\Pages\EditChannelStream;
use App\Filament\Resources\ChannelStreams\Pages\ListChannelStreams;
use App\Filament\Resources\ChannelStreams\Schemas\ChannelStreamForm;
use App\Filament\Resources\ChannelStreams\Tables\ChannelStreamsTable;
use App\Models\ChannelStream;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChannelStreamResource extends Resource
{
    protected static ?string $model = ChannelStream::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Tv;

    protected static ?string $modelLabel = 'Kanál';

    protected static ?string $pluralModelLabel = 'Streamované kanály';

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isGloballySearchable = false;

    public static function canAccess(): bool
    {
        return auth()->user()->role === RoleEnum::ADMIN;
    }

    public static function form(Schema $schema): Schema
    {
        return ChannelStreamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChannelStreamsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChannelStreams::route('/'),
            'create' => CreateChannelStream::route('/create'),
            'edit' => EditChannelStream::route('/{record}/edit'),
        ];
    }
}
