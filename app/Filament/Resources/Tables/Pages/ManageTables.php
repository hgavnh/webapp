<?php

namespace App\Filament\Resources\Tables\Pages;

use App\Filament\Resources\Tables\TableResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTables extends ManageRecords
{
    protected static string $resource = TableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('bulkCreateTables')
                ->label(__('ui.table.actions.bulk_create_tables'))
                ->icon('heroicon-o-rectangle-stack')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\Select::make('room_id')
                        ->label(__('ui.table.fields.room_id'))
                        ->relationship('room', 'name')
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('prefix')
                        ->label(__('ui.table.actions.prefix'))
                        ->default('Bàn ')
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('start_number')
                        ->label(__('ui.table.actions.start_number'))
                        ->numeric()
                        ->default(1)
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('count')
                        ->label(__('ui.table.actions.count'))
                        ->numeric()
                        ->default(10)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $prefix = $data['prefix'];
                    $start = (int)$data['start_number'];
                    $count = (int)$data['count'];
                    $roomId = $data['room_id'];
                    $tenantId = auth()->user()?->tenant_id ?? 1;

                    for ($i = 0; $i < $count; $i++) {
                        \App\Models\Table::create([
                            'tenant_id' => $tenantId,
                            'room_id' => $roomId,
                            'name' => $prefix . ($start + $i),
                            'status' => 'available',
                            'capacity' => 4,
                        ]);
                    }

                    \Filament\Notifications\Notification::make()
                        ->title(__('ui.table.actions.success_msg', ['count' => $count]))
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
