<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant_id')
                    ->label(__('ui.product.fields.tenant_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('category_id')
                    ->label(__('ui.product.fields.category_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('ui.product.fields.name'))
                    ->searchable(),
                TextColumn::make('price')
                    ->label(__('ui.product.fields.price'))
                    ->money('VND')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('ui.product.fields.is_active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('ui.product.fields.created_at'))
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
