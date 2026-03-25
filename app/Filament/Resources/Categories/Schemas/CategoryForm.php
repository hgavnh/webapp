<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant_id')
                    ->label(__('ui.category.fields.tenant_id'))
                    ->numeric()
                    ->default(null),
                TextInput::make('name')
                    ->label(__('ui.category.fields.name'))
                    ->required(),
                TextInput::make('icon')
                    ->label(__('ui.category.fields.icon'))
                    ->default('☕'),
                TextInput::make('sort_order')
                    ->label(__('ui.category.fields.sort_order'))
                    ->numeric()
                    ->default(0),
            ]);
    }
}
