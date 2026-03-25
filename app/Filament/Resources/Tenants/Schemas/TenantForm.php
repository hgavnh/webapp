<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('subdomain')
                    ->default(null),
                TextInput::make('plan_type')
                    ->required()
                    ->default('free'),
                TextInput::make('status')
                    ->label('Trạng thái')
                    ->required()
                    ->default('active'),
                \Filament\Forms\Components\Select::make('business_type')
                    ->label(__('ui.business_type.label'))
                    ->options([
                        'retail' => __('ui.business_type.options.retail'),
                        'cafe' => __('ui.business_type.options.cafe'),
                    ])
                    ->required()
                    ->default('retail'),
                ColorPicker::make('theme_color')
                    ->label('Theme màu')
                    ->default('#4CAF50'),
            ]);
    }
}
