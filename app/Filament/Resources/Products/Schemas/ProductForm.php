<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant_id')
                    ->label(__('ui.product.fields.tenant_id'))
                    ->numeric()
                    ->default(null),
                TextInput::make('category_id')
                    ->label(__('ui.product.fields.category_id'))
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->label(__('ui.product.fields.name'))
                    ->required(),
                TextInput::make('price')
                    ->label(__('ui.product.fields.price'))
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->suffix('VNĐ'),
                Toggle::make('is_active')
                    ->label(__('ui.product.fields.is_active')),
            ]);
    }
}
