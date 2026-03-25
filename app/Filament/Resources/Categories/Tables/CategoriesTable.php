<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant_id')
                    ->label(__('ui.category.fields.tenant_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('ui.category.fields.name'))
                    ->searchable(),
                TextColumn::make('icon')
                    ->label(__('ui.category.fields.icon'))
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label(__('ui.category.fields.sort_order'))
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
