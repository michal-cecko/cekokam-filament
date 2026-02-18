<?php

namespace App\Filament\Resources\Servers;

use App\Filament\Resources\Servers\Pages\CreateServer;
use App\Filament\Resources\Servers\Pages\EditServer;
use App\Filament\Resources\Servers\Pages\ListServers;
use App\Filament\Resources\Servers\Schemas\ServerForm;
use App\Filament\Resources\Servers\Tables\ServersTable;
use App\Models\Other\Server;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class ServerResource extends Resource
{
    protected static ?string $model = Server::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static ?string $modelLabel = 'Rozsah';

    protected static ?string $pluralModelLabel = 'Rozsahy';

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isGloballySearchable = true;

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        return $record?->full_name;
    }

    public static function form(Schema $schema): Schema
    {
        return ServerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServersTable::configure($table);
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
            'index' => ListServers::route('/'),
            'create' => CreateServer::route('/create'),
            'edit' => EditServer::route('/{record}/edit'),
        ];
    }
}
