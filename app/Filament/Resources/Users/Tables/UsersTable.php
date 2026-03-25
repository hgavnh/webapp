<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('ui.user.fields.name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('ui.user.fields.email'))
                    ->searchable(),
                TextColumn::make('role')
                    ->label(__('ui.user.fields.role'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'admin' => 'danger',
                        'cashier' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'admin' => __('ui.user.roles.admin'),
                        'cashier' => __('ui.user.roles.cashier'),
                        default => $state,
                    }),
                ToggleColumn::make('is_active')
                    ->label(__('ui.user.fields.is_active')),
                TextColumn::make('created_at')
                    ->label(__('ui.user.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
