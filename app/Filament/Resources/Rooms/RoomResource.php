<?php

namespace App\Filament\Resources\Rooms;

use App\Filament\Resources\Rooms\Pages\ManageRooms;
use App\Models\Room;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationLabel(): string
    {
        return __('ui.nav.rooms');
    }

    public static function getModelLabel(): string
    {
        return __('ui.room.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ui.room.plural_label');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->tenant?->business_type === 'cafe';
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('ui.room.fields.name'))
                    ->required(),
                TextInput::make('sort_order')
                    ->label(__('ui.room.fields.sort_order'))
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('ui.room.fields.name'))
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label(__('ui.room.fields.sort_order'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('ui.category.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRooms::route('/'),
        ];
    }
}
