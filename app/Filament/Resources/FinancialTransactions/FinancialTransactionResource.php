<?php

namespace App\Filament\Resources\FinancialTransactions;

use App\Filament\Resources\FinancialTransactions\Pages\ManageFinancialTransactions;
use App\Models\FinancialTransaction;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FinancialTransactionResource extends Resource
{
    protected static ?string $model = FinancialTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationLabel(): string
    {
        return __('ui.financial.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('ui.financial.nav_group');
    }

    public static function getModelLabel(): string
    {
        return __('ui.financial.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ui.financial.title');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label(__('ui.financial.type'))
                    ->options([
                        'income' => __('ui.financial.income'),
                        'expense' => __('ui.financial.expense'),
                    ])
                    ->required()
                    ->native(false),
                TextInput::make('amount')
                    ->label(__('ui.financial.amount'))
                    ->required()
                    ->numeric()
                    ->prefix('VNĐ')
                    ->placeholder('0'),
                TextInput::make('category')
                    ->label(__('ui.financial.category'))
                    ->placeholder('Ăn uống, Tiền điện, Nhập hàng...')
                    ->default(null),
                DateTimePicker::make('transaction_date')
                    ->label(__('ui.financial.transaction_date'))
                    ->default(now())
                    ->required(),
                Textarea::make('note')
                    ->label(__('ui.financial.note'))
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(__('ui.financial.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'income' => 'success',
                        'expense' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'income' => __('ui.financial.income'),
                        'expense' => __('ui.financial.expense'),
                    }),
                TextColumn::make('amount')
                    ->label(__('ui.financial.amount'))
                    ->money('VND')
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Tổng cộng')),
                TextColumn::make('category')
                    ->label(__('ui.financial.category'))
                    ->searchable(),
                TextColumn::make('transaction_date')
                    ->label(__('ui.financial.transaction_date'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('ui.financial.user'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
            'index' => ManageFinancialTransactions::route('/'),
        ];
    }
}
