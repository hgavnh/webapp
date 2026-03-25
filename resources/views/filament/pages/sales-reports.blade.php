<x-filament-panels::page>
    <style>
        .fi-wi-stats-overview-stat {
            padding-top: 2.5rem !important;
            padding-bottom: 2.5rem !important;
            transition: all 0.3s ease;
        }
        .fi-wi-stats-overview-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        }
        .fi-wi-stats-overview-stat > div {
            gap: 1.25rem !important;
        }
        .fi-wi-chart-widget {
            padding: 2rem !important;
        }
        @media print {
            @page {
                margin: 0; /* Xóa bỏ header/footer mặc định của trình duyệt (URL, ngày tháng) */
            }
            :root {
                color-scheme: light !important;
            }

            /* ÉP ĐEN TOÀN BỘ CHỮ - KHÔNG NGOẠI LỆ */
            * {
                color: #000000 !important;
                background-color: transparent !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }

            /* ÉP TRẮNG CÁC LỚP NỀN CHÍNH */
            html, body {
                background-color: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .fi-main {
                padding: 1.5cm !important; /* Tạo lề thủ công cho nội dung báo cáo */
            }

            /* Ẩn UI Filament */
            .fi-sidebar, .fi-topbar, .fi-header, .fi-header-actions, 
            .period-selector-container, .fi-breadcrumbs, .fi-logo,
            button, a, nav, [role="navigation"], [role="banner"],
            .fi-wi-stats-overview, .fi-wi-chart {
                display: none !important;
            }

            /* Bố cục in báo cáo */
            .print-table-container {
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
                background-color: #ffffff !important;
            }

            .print-table {
                width: 100% !important;
                border: 2px solid #000000 !important;
                border-collapse: collapse !important;
            }

            .print-table th {
                background-color: #f2f2f2 !important;
                border: 1px solid #000000 !important;
                padding: 12px !important;
                color: #000 !important;
            }

            .print-table td {
                border: 1px solid #000000 !important;
                padding: 10px !important;
                color: #000 !important;
            }

            .print-only {
                display: block !important;
                text-align: center;
                margin-bottom: 2cm !important;
            }
        }
        .print-only { display: none; }
        @media screen {
            .print-table-container { display: none; }
        }
        @media print {
            .print-table-container { display: block !important; }
            .print-table th, .print-table td { border: 1px solid #eee !important; }
        }
		.space-padding {
			padding-top: 10px;
			padding-bottom: 10px;
		}
    </style>

    <div class="print-only">
        <h2 class="text-3xl font-bold text-center text-gray-900">{{ __('ui.nav.sales_report') }}</h2>
        <p class="text-center text-gray-500 mt-2">{{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="space-y-10">
        {{-- Bộ lọc --}}
        <div class="flex flex-col gap-4 period-selector-container space-padding">
            <!--<h3 class="text-xs font-bold text-gray-400 uppercase tracking-[0.2em] ml-1">{{ __('ui.reports.time_config') }}</h3>-->
            {{ $this->form }}
        </div>

        {{-- Chỉ số --}}
        <div class="space-y-6 space-padding">
            <!--<h3 class="text-xs font-bold text-gray-400 uppercase tracking-[0.2em] ml-1">{{ __('ui.reports.overview_stats') }}</h3>-->
            @livewire(\App\Filament\Widgets\SalesOverview::class, ['filter' => $this->data['period'] ?? 'day'], key('stats-' . ($this->data['period'] ?? 'day')))
        </div>

        {{-- Biểu đồ --}}
        <div class="space-y-6 space-padding">
            <!--<h3 class="text-xs font-bold text-gray-400 uppercase tracking-[0.2em] ml-1">{{ __('ui.reports.trend_analysis') }}</h3>-->
            <div class="grid grid-cols-1 gap-8">
                @livewire(\App\Filament\Widgets\SalesChart::class, ['filter' => $this->data['period'] ?? 'day'], key('chart-' . ($this->data['period'] ?? 'day')))
            </div>
        </div>

        {{-- Bảng chi tiết chỉ hiển thị khi IN --}}
        <div class="print-table-container">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-[0.2em] ml-1 print-only" style="margin-bottom: 1rem;">{{ __('ui.reports.detail_report') }}</h3>
            <table class="w-full text-left border-collapse print-table">
                <thead>
                    <tr class="bg-gray-50 border-y border-gray-200">
                        <th class="px-4 py-3 text-[12px] font-bold text-gray-700 uppercase">{{ __('ui.reports.table.order_code') }}</th>
                        <th class="px-4 py-3 text-[12px] font-bold text-gray-700 uppercase">{{ __('ui.reports.table.time') }}</th>
                        <th class="px-4 py-3 text-[12px] font-bold text-gray-700 uppercase">{{ __('ui.reports.table.table') }}</th>
                        <th class="px-4 py-3 text-[12px] font-bold text-gray-700 uppercase">{{ __('ui.customer.customer_name') }}</th>
                        <th class="px-4 py-3 text-[12px] font-bold text-gray-700 uppercase">{{ __('ui.customer.customer_phone') }}</th>
                        <th class="px-4 py-3 text-[12px] font-bold text-gray-700 uppercase">{{ __('ui.reports.table.products') }}</th>
                        <th class="px-4 py-3 text-[12px] font-bold text-gray-700 uppercase text-right">{{ __('ui.reports.table.total') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php $sum = 0; @endphp
                    @foreach($reportOrders as $order)
                        @php $sum += $order->total; @endphp
                        <tr>
                            <td class="px-4 py-3 text-[13px] text-gray-600 font-medium">{{ $order->order_code }}</td>
                            <td class="px-4 py-3 text-[13px] text-gray-500 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('H:i d/m') }}</td>
                            <td class="px-4 py-3 text-[13px] text-gray-600">{{ $order->table ? $order->table->name : 'KV Bán lẻ' }}</td>
                            <td class="px-4 py-3 text-[13px] text-gray-600">{{ $order->customer_name ?? 'Khách lẻ' }}</td>
                            <td class="px-4 py-3 text-[13px] text-gray-600">{{ $order->customer_phone ?? '-' }}</td>
                            <td class="px-4 py-3 text-[12px] text-gray-500 italic max-w-xs truncate">
                                {{ $order->items->map(fn($i) => ($i->product->name ?? $i->product_name) . " (x{$i->qty})")->implode(', ') }}
                            </td>
                            <td class="px-4 py-3 text-[13px] text-gray-800 font-bold text-right">{{ number_format($order->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50/50 font-bold border-t-2 border-gray-800">
                        <td colspan="6" class="px-4 py-4 text-[14px] text-gray-800 uppercase tracking-wider text-right">{{ __('ui.reports.total_revenue') }}</td>
                        <td class="px-4 py-4 text-[16px] text-green-700 text-right">{{ number_format($sum, 0, ',', '.') }} VNĐ</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script>
        window.addEventListener('beforeprint', () => {
            document.documentElement.classList.remove('dark');
        });
        window.addEventListener('afterprint', () => {
            // Khôi phục lại dark mode nếu trước đó có (check localStorage của Filament)
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        });
    </script>
</x-filament-panels::page>
