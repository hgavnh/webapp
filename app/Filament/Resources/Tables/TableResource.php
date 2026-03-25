<?php

namespace App\Filament\Resources\Tables;

use App\Filament\Resources\Tables\Pages\ManageTables;
use App\Models\Table as Table1;
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

class TableResource extends Resource
{
    protected static ?string $model = Table1::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationLabel(): string
    {
        return __('ui.nav.tables');
    }

    public static function getModelLabel(): string
    {
        return __('ui.table.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ui.table.plural_label');
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
                \Filament\Forms\Components\Select::make('room_id')
                    ->label(__('ui.table.fields.room_id'))
                    ->relationship('room', 'name')
                    ->required(),
                TextInput::make('name')
                    ->label(__('ui.table.fields.name'))
                    ->required(),
                \Filament\Forms\Components\Select::make('status')
                    ->label(__('ui.table.fields.status'))
                    ->options([
                        'available' => __('ui.table.statuses.available'),
                        'occupied' => __('ui.table.statuses.occupied'),
                        'reserved' => __('ui.table.statuses.reserved'),
                    ])
                    ->required()
                    ->default('available'),
                TextInput::make('capacity')
                    ->label(__('ui.table.fields.capacity'))
                    ->required()
                    ->numeric()
                    ->default(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('room.name')
                    ->label(__('ui.table.fields.room_id'))
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('ui.table.fields.name'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('ui.table.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'danger',
                        'reserved' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("ui.table.statuses.{$state}")),
                TextColumn::make('capacity')
                    ->label(__('ui.table.fields.capacity'))
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\Action::make('checkout')
                    ->label('Thanh toán')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Thanh toán & Trả bàn')
                    ->modalDescription('Hành động này sẽ hoàn thành tất cả đơn hàng tại bàn này và đưa bàn về trạng thái Trống.')
                    ->action(function (\App\Models\Table $record) {
                        // Tìm các đơn hàng chưa hoàn thành của bàn này
                        \App\Models\Order::where('table_id', $record->id)
                            ->whereIn('status', ['pending', 'processing'])
                            ->update(['status' => 'completed']);
                        
                        $record->update(['status' => 'available']);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Đã thanh toán và trả bàn công!')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (\App\Models\Table $record) => $record->status === 'occupied'),
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
            'index' => ManageTables::route('/'),
        ];
    }
}
