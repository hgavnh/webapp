<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\Product;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant_id')
                    ->label(__('ui.order.fields.tenant_id'))
                    ->numeric()
                    ->default(fn () => auth()->user()?->tenant_id)
                    ->readOnly(),
                Select::make('table_id')
                    ->label(__('ui.order.fields.table_id'))
                    ->relationship('table', 'name', fn ($query) => $query->where('tenant_id', auth()->user()?->tenant_id))
                    ->searchable()
                    ->default(null)
                    ->visible(fn () => auth()->user()?->tenant?->business_type === 'cafe'),
                TextInput::make('order_code')
                    ->label(__('ui.order.fields.order_code'))
                    ->required()
                    ->default(fn () => 'HD-' . strtoupper(\Illuminate\Support\Str::random(6)))
                    ->readOnly(),
                TextInput::make('customer_name')
                    ->label(__('ui.customer.customer_name'))
                    ->visible(fn () => auth()->user()?->tenant?->business_type === 'retail'),
                TextInput::make('customer_phone')
                    ->label(__('ui.customer.customer_phone'))
                    ->visible(fn () => auth()->user()?->tenant?->business_type === 'retail'),
                Textarea::make('note')
                    ->label(__('ui.order.fields.note'))
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('cashier_id')
                    ->label(__('ui.order.fields.cashier_id'))
                    ->numeric()
                    ->default(null),
                Select::make('status')
                    ->label(__('ui.order.fields.status'))
                    ->options([
                        'pending' => __('ui.order.status.pending'),
                        'processing' => __('ui.order.status.processing'),
                        'completed' => __('ui.order.status.completed'),
                        'cancelled' => __('ui.order.status.cancelled'),
                    ])
                    ->default('pending'),

                Repeater::make('items')
                    ->label(__('ui.order.fields.items'))
                    ->relationship('items')
                    ->schema([
                        Select::make('product_id')
                            ->label(__('ui.order.item.product'))
                            ->options(fn () => Product::where('tenant_id', auth()->user()?->tenant_id)->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $product = Product::find($state);
                                if ($product) {
                                    $set('product_name', $product->name);
                                    $set('price', $product->price);
                                    $set('subtotal', $product->price * ($get('qty') ?? 1));
                                }
                                self::updateOrderTotal($get, $set, true);
                            })
                            ->columnSpan(2),
                        TextInput::make('product_name')
                            ->label(__('ui.order.item.name'))
                            ->columnSpan(2),
                        TextInput::make('price')
                            ->label(__('ui.order.item.price'))
                            ->numeric()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                $set('subtotal', ($state ?? 0) * ($get('qty') ?? 0));
                                self::updateOrderTotal($get, $set, true);
                            })
                            ->columnSpan(2),
                        TextInput::make('qty')
                            ->label(__('ui.order.item.qty'))
                            ->numeric()
                            ->default(1)
                            ->reactive()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                $set('subtotal', ($get('price') ?? 0) * ($state ?? 1));
                                self::updateOrderTotal($get, $set, true);
                            })
                            ->columnSpan(2),
                        TextInput::make('subtotal')
                            ->label(__('ui.order.item.subtotal'))
                            ->numeric()
                            ->readOnly()
                            ->columnSpan(2),
                    ])
                    ->columns(10)
                    ->columnSpanFull()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        self::updateOrderTotal($get, $set);
                    }),
                
                TextInput::make('total')
                    ->label(__('ui.order.fields.total'))
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->readOnly()
                    ->prefix('VNĐ')
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'text-2xl font-bold text-primary-600']),
            ]);
    }

    public static function updateOrderTotal(Get $get, Set $set, bool $isItemLevel = false): void
    {
        // Nếu ở cấp Item, lấy items từ cấp cha
        $items = $isItemLevel ? $get('../../items') : $get('items');
        $items = $items ?? [];
        
        $total = 0;
        foreach ($items as $item) {
            $price = floatval($item['price'] ?? 0);
            $qty = floatval($item['qty'] ?? 0);
            $total += $price * $qty;
        }
        
        // Gán lại vào root field 'total'
        if ($isItemLevel) {
            $set('../../total', $total);
        } else {
            $set('total', $total);
        }
    }
}
