<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('print')
                ->label(__('ui.order.actions.print_bill'))
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn (Order $record) => '#')
                ->extraAttributes(fn (Order $record) => [
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
            DeleteAction::make(),
        ];
    }
}
