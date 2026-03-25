<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill - {{ $order->order_code }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            color: #000;
            background-color: #fff;
            font-size: 14px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .header { margin-bottom: 5mm; }
        .shop-name { font-size: 18px; margin-bottom: 2mm; }
        .divider { border-bottom: 1px dashed #000; margin: 4mm 0; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 1mm; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 3mm; }
        .items-table th { border-bottom: 1px solid #000; text-align: left; padding-bottom: 1mm; }
        .items-table td { padding: 2mm 0; vertical-align: top; }
        .total-section { margin-top: 5mm; }
        .total-row { display: flex; justify-content: space-between; font-size: 16px; font-weight: bold; }
        .footer { margin-top: 10mm; font-size: 12px; }
        
        @media print {
            body { 
                width: 80mm; 
                padding: 0;
                margin: 0;
            }
            @page {
                margin: 0;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <script>
        window.onload = function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            }
        }
    </script>
    <div class="header text-center">
        <div class="shop-name bold">{{ $order->tenant->name }}</div>
        <div class="bold">{{ __('ui.customer.menu_title') }}</div>
        <div class="divider"></div>
        <div class="bold" style="font-size: 16px;">{{ strtoupper(__('ui.customer.checkout')) }}</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span>{{ __('ui.reports.table.order_code') }}:</span>
            <span class="bold">{{ $order->order_code }}</span>
        </div>
        @if($order->table)
        <div class="info-row">
            <span>{{ __('ui.reports.table.table') }}:</span>
            <span class="bold">{{ $order->table->name }}</span>
        </div>
        @endif
        @if($order->customer_name)
        <div class="info-row">
            <span>{{ __('ui.customer.customer_name') }}:</span>
            <span class="bold">{{ $order->customer_name }}</span>
        </div>
        @endif
        @if($order->customer_phone)
        <div class="info-row">
            <span>{{ __('ui.customer.customer_phone') }}:</span>
            <span class="bold">{{ $order->customer_phone }}</span>
        </div>
        @endif
        <div class="info-row">
            <span>{{ __('ui.reports.table.time') }}:</span>
            <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="50%">{{ __('ui.order.item.name') }}</th>
                <th width="15%" class="text-center">{{ __('ui.order.item.qty') }}</th>
                <th width="35%" class="text-right">{{ __('ui.order.item.subtotal') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td class="text-center">{{ $item->qty }}</td>
                <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="total-section">
        <div class="total-row">
            <span>{{ strtoupper(__('ui.customer.total')) }}</span>
            <span>{{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
    </div>

    @if($order->note)
    <div style="margin-top: 5mm; font-style: italic; border: 1px solid #000; padding: 2mm;">
        {{ __('ui.customer.note') }}: {{ $order->note }}
    </div>
    @endif

    <div class="footer text-center">
        <p>Cảm ơn quý khách - Hẹn gặp lại!</p>
        <p>Powered by HungThinh SaaS</p>
    </div>

    <div class="no-print text-center" style="margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Print Again</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; margin-left: 10px;">Close Window</button>
    </div>
</body>
</html>
