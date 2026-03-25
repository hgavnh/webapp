<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesOverview extends BaseWidget
{
    public ?string $filter = 'day';

    protected function getStats(): array
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        
        $query = Order::where('tenant_id', $tenantId)
            ->where(function($q) {
                $q->whereIn('status', ['completed', 'pending', 'processing', 'paid', 'success'])
                  ->orWhereNull('status')
                  ->orWhere('status', '');
            });

        $now = Carbon::now();

        if ($this->filter === 'day') {
            $query->whereDate('created_at', $now->toDateString());
        } elseif ($this->filter === 'week') {
            $query->whereBetween('created_at', [$now->startOfWeek()->toDateTimeString(), $now->endOfWeek()->toDateTimeString()]);
        } elseif ($this->filter === 'month') {
            $query->whereMonth('created_at', $now->month)
                  ->whereYear('created_at', $now->year);
        } elseif ($this->filter === 'quarter') {
            $query->whereBetween('created_at', [$now->startOfQuarter()->toDateTimeString(), $now->endOfQuarter()->toDateTimeString()]);
        } elseif ($this->filter === 'year') {
            $query->whereYear('created_at', $now->year);
        }

        $orders = $query->get();
        $totalRevenue = $orders->sum('total');
        $orderCount = $orders->count();
        $avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        return [
            Stat::make(__('ui.reports.revenue'), number_format($totalRevenue, 0, ',', '.') . ' VNĐ')
                ->description(__('ui.reports.revenue_desc'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make(__('ui.reports.orders'), $orderCount)
                ->description(__('ui.reports.orders_desc'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
            Stat::make(__('ui.reports.avg_v'), number_format($avgOrderValue, 0, ',', '.') . ' VNĐ')
                ->description(__('ui.reports.avg_v_desc'))
                ->descriptionIcon('heroicon-m-presentation-chart-line')
                ->color('warning'),
        ];
    }
}
