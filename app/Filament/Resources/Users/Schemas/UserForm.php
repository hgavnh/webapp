<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('ui.user.fields.name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('ui.user.fields.email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label(__('ui.user.fields.password'))
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),
                Select::make('role')
                    ->label(__('ui.user.fields.role'))
                    ->options([
                        'admin' => __('ui.user.roles.admin'),
                        'cashier' => __('ui.user.roles.cashier'),
                    ])
                    ->required()
                    ->default('cashier'),
                Toggle::make('is_active')
                    ->label(__('ui.user.fields.is_active'))
                    ->default(true),
            ]);
    }
}
