<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant_id')
                    ->label(__('ui.order.fields.tenant_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('table_id')
                    ->label(__('ui.order.fields.table_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('order_code')
                    ->label(__('ui.order.fields.order_code'))
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label(__('ui.customer.customer_name'))
                    ->searchable()
                    ->visible(fn () => auth()->user()?->tenant?->business_type === 'retail'),
                TextColumn::make('customer_phone')
                    ->label(__('ui.customer.customer_phone'))
                    ->searchable()
                    ->visible(fn () => auth()->user()?->tenant?->business_type === 'retail'),
                TextColumn::make('total')
                    ->label(__('ui.order.fields.total'))
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('cashier_id')
                    ->label(__('ui.order.fields.cashier_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('ui.order.fields.status'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => __('ui.order.status.pending'),
                        'processing' => __('ui.order.status.processing'),
                        'completed' => __('ui.order.status.completed'),
                        'cancelled' => __('ui.order.status.cancelled'),
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label(__('ui.order.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\Action::make('process')
                    ->label(__('ui.order.actions.process'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (\App\Models\Order $record) {
                        $record->update(['status' => 'processing']);
                        \Filament\Notifications\Notification::make()
                            ->title(__('ui.order.actions.process_notification'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (\App\Models\Order $record) => $record->status === 'pending'),
                \Filament\Actions\Action::make('print')
                    ->label(__('ui.order.actions.print_bill'))
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (\App\Models\Order $record) => '#')
                    ->extraAttributes(fn (\App\Models\Order $record) => [
                        'onclick' => "
                            var iframe = document.getElementById('print-iframe');
                            if (!iframe) {
                                iframe = document.createElement('iframe');
                                iframe.id = 'print-iframe';
                                iframe.style.display = 'none';
                                document.body.appendChild(iframe);
                            }
                            iframe.src = '" . route('order.receipt', $record) . "';
                            event.preventDefault();
                            return false;
                        "
                    ]),
                \Filament\Actions\Action::make('complete')
                    ->label(__('ui.order.actions.complete'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (\App\Models\Order $record) {
                        $record->update(['status' => 'completed']);
                        \Filament\Notifications\Notification::make()
                            ->title(__('ui.order.actions.complete_notification'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (\App\Models\Order $record) => in_array($record->status, ['pending', 'processing'])),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->poll('3s')
            ->defaultSort('created_at', 'desc');
    }
}
